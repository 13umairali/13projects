<?php

namespace App\Support;

use Illuminate\Support\Arr;

/**
 * Thin accessor over config/admin_resources.php. Resolves resource definitions
 * by key, exposes them grouped for the sidebar, and derives validation rules
 * and table columns from each definition's field schema.
 */
class ResourceRegistry
{
    /** All resource definitions keyed by their route slug. */
    public static function all(): array
    {
        return config('admin_resources', []);
    }

    /** Every registered resource key (used to constrain the {resource} route param). */
    public static function keys(): array
    {
        return array_keys(static::all());
    }

    /** Resolve a single definition, enriched with its own key, or null. */
    public static function find(string $key): ?array
    {
        $def = static::all()[$key] ?? null;

        return $def ? array_merge($def, ['key' => $key]) : null;
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, static::all());
    }

    /**
     * Definitions grouped by their `group` label for sidebar rendering,
     * each enriched with its key.
     */
    public static function grouped(): array
    {
        $grouped = [];

        foreach (static::all() as $key => $def) {
            $grouped[$def['group'] ?? 'Content'][] = array_merge($def, ['key' => $key]);
        }

        return $grouped;
    }

    /** Laravel validation rules built from the field schema: [field => rules[]]. */
    public static function rules(array $def): array
    {
        $rules = [];

        foreach ($def['fields'] ?? [] as $field) {
            $rules[$field['name']] = $field['rules'] ?? ['nullable'];
        }

        return $rules;
    }

    /** Fields flagged with list => true, for the index table columns. */
    public static function listColumns(array $def): array
    {
        return array_values(array_filter(
            $def['fields'] ?? [],
            fn ($field) => $field['list'] ?? false
        ));
    }

    /**
     * Names of the file-upload fields. A definition with any of these must be
     * submitted as multipart — JSON silently drops UploadedFile instances.
     */
    public static function imageFields(array $def): array
    {
        return array_values(array_map(
            fn ($field) => $field['name'],
            array_filter($def['fields'] ?? [], fn ($field) => ($field['type'] ?? '') === 'image')
        ));
    }

    /** Backend public-disk directory this resource's images live in. */
    public static function storageDir(array $def): ?string
    {
        return $def['storage'] ?? null;
    }

    /**
     * Normalise a list payload into a plain array of associative rows.
     * Transparently unwraps a Laravel paginator envelope ({ data: [...] }).
     */
    public static function normalizeList(mixed $data): array
    {
        if (is_array($data) && Arr::isAssoc($data) && isset($data['data']) && is_array($data['data'])) {
            $data = $data['data'];
        }

        if (! is_array($data)) {
            return [];
        }

        return array_map(fn ($row) => (array) $row, array_values($data));
    }
}
