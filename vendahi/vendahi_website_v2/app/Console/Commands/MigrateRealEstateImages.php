<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Models\PropertyCategory;
use App\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Real estate uploads used to land in one shared public/images/properties/
 * folder for every tenant. They are now written under upload_path('properties')
 * like the rest of the app. Old rows keep working because the stored path still
 * resolves, but the files are not tenant-scoped -- this moves them across.
 *
 * Only files a tenant's own rows reference are moved, so nothing crosses from
 * one tenant to another. Anything left behind in the shared folder afterwards
 * is unreferenced and reported rather than deleted.
 */
class MigrateRealEstateImages extends Command
{
    protected $signature = 'realestate:migrate-images
                            {--tenant= : Only this tenant id}
                            {--dry-run : Report what would move without touching anything}';

    protected $description = 'Move legacy public/images/properties uploads into each tenant\'s own upload folder';

    private const LEGACY_PREFIX = 'images/properties/';

    private bool $dry = false;

    /** Basenames claimed by some tenant, to spot anything unreferenced at the end. */
    private array $claimed = [];

    public function handle(): int
    {
        $this->dry = (bool) $this->option('dry-run');
        if ($this->dry) {
            $this->warn('Dry run -- no files moved, no rows updated.');
        }

        $tenants = Tenant::query()
            ->when($this->option('tenant'), fn ($q) => $q->where('id', $this->option('tenant')))
            ->get();

        if ($tenants->isEmpty()) {
            $this->error('No matching tenants.');

            return self::FAILURE;
        }

        $moved = 0;
        foreach ($tenants as $tenant) {
            $this->info("[{$tenant->id}]");
            try {
                tenancy()->initialize($tenant);
                $moved += $this->migrateTenant($tenant);
            } catch (\Throwable $e) {
                $this->warn('  skipped: '.$e->getMessage());
            } finally {
                try {
                    tenancy()->end();
                } catch (\Throwable $e) {
                    // nothing to end
                }
            }
        }

        $this->newLine();
        $this->info($this->dry ? "{$moved} file(s) would move." : "{$moved} file(s) moved.");
        $this->reportLeftovers();

        return self::SUCCESS;
    }

    private function migrateTenant(Tenant $tenant): int
    {
        $target = upload_path('properties');
        $moved = 0;

        foreach (Property::query()->get(['id', 'featured_image', 'gallery']) as $property) {
            $updates = [];

            $featured = $this->relocate($property->featured_image, $target, $moved);
            if ($featured !== null) {
                $updates['featured_image'] = $featured;
            }

            $gallery = $property->gallery;
            if (is_array($gallery) && $gallery) {
                $newGallery = [];
                $changed = false;
                foreach ($gallery as $path) {
                    $new = $this->relocate($path, $target, $moved);
                    $newGallery[] = $new ?? $path;
                    $changed = $changed || $new !== null;
                }
                if ($changed) {
                    $updates['gallery'] = $newGallery;
                }
            }

            if ($updates) {
                $this->line("  property #{$property->id}: ".implode(', ', array_keys($updates)));
                if (! $this->dry) {
                    Property::query()->whereKey($property->id)->update(
                        array_map(fn ($v) => is_array($v) ? json_encode($v) : $v, $updates)
                    );
                }
            }
        }

        foreach (PropertyCategory::query()->get(['id', 'image']) as $category) {
            $new = $this->relocate($category->image, $target, $moved);
            if ($new !== null) {
                $this->line("  category #{$category->id}: image");
                if (! $this->dry) {
                    PropertyCategory::query()->whereKey($category->id)->update(['image' => $new]);
                }
            }
        }

        return $moved;
    }

    /**
     * Move one legacy path's file into $target and return its new relative path,
     * or null when the path is not a legacy one (already scoped, absolute URL…).
     */
    private function relocate(?string $path, string $target, int &$moved): ?string
    {
        if (! $path || ! str_starts_with($path, self::LEGACY_PREFIX)) {
            return null;
        }

        $name = basename($path);
        $this->claimed[$name] = true;

        $from = public_path(self::LEGACY_PREFIX.$name);
        $to = public_path($target.'/'.$name);
        $new = $target.'/'.$name;

        if (is_file($to)) {
            // Already moved on an earlier run; just repoint the row.
            return $new;
        }

        if (! is_file($from)) {
            $this->warn("  missing file, row left as-is: {$path}");

            return null;
        }

        if (! $this->dry) {
            File::ensureDirectoryExists(dirname($to));
            File::move($from, $to);
        }
        $moved++;

        return $new;
    }

    private function reportLeftovers(): void
    {
        $dir = public_path(rtrim(self::LEGACY_PREFIX, '/'));
        if (! is_dir($dir)) {
            return;
        }

        $left = collect(File::files($dir))
            ->map(fn ($f) => $f->getFilename())
            ->reject(fn ($n) => isset($this->claimed[$n]))
            ->values();

        if ($left->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->warn("{$left->count()} file(s) in {$dir} are referenced by no property or category.");
        $this->line('  Review before deleting -- they may belong to a tenant this run skipped.');
    }
}
