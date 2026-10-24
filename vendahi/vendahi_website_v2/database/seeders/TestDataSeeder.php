<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Generic test-data filler.
 *
 * Introspects information_schema for the current (tenant) database and inserts
 * a few rows into every table that is still empty, in foreign-key dependency
 * order. Values are derived from column types, enum definitions, FK targets
 * and column-name heuristics, so the rows satisfy the schema's constraints
 * even for tables the hand-written seeders don't know about.
 *
 * Intended for dev/test tenants only. Tables that already contain rows are
 * never touched, so it is safe to re-run.
 */
class TestDataSeeder extends Seeder
{
    private const ROWS_PER_TABLE = 3;

    /** Framework bookkeeping tables that stay empty on purpose. */
    private const SKIP_TABLES = [
        'migrations', 'password_resets', 'failed_jobs', 'jobs',
        'oauth_access_tokens', 'oauth_auth_codes', 'oauth_clients',
        'oauth_personal_access_clients', 'oauth_refresh_tokens',
    ];

    /** @var array<string, array<int, int|string>> cached existing PK values per table */
    private array $refIds = [];

    /** @var array<int, array{table:string, column:string, ref:string}> */
    private array $fixups = [];

    /** @var string */
    private $schema;

    /** @var string lazily computed bcrypt hash shared by all generated rows */
    private $passwordHash;

    public function run(): void
    {
        $this->schema = DB::getDatabaseName();

        $tables  = $this->emptyTables();
        $columns = $this->columnsByTable(array_keys($tables));
        $fks     = $this->foreignKeysByTable();
        $uniques = $this->uniqueColumnsByTable();

        $ordered = $this->topoSort(array_keys($tables), $fks);

        $now = Carbon::now();
        $failed = [];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($ordered as $table) {
                try {
                    $this->fillTable($table, $columns[$table] ?? [], $fks[$table] ?? [], $uniques[$table] ?? [], $now);
                    $this->command?->line("  seeded: {$table}");
                } catch (\Throwable $e) {
                    $failed[$table] = $e->getMessage();
                    $this->command?->warn("  FAILED: {$table} — " . Str::limit($e->getMessage(), 160));
                }
            }
            $this->applyFixups();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->command?->info(sprintf('Done: %d tables seeded, %d failed.', count($ordered) - count($failed), count($failed)));
    }

    // ------------------------------------------------------------------
    // Schema introspection
    // ------------------------------------------------------------------

    /** @return array<string, true> empty, seedable base tables */
    private function emptyTables(): array
    {
        $names = array_column(DB::select(
            "SELECT table_name AS t FROM information_schema.tables
             WHERE table_schema = ? AND table_type = 'BASE TABLE'", [$this->schema]
        ), 't');

        $empty = [];
        foreach ($names as $name) {
            if (in_array($name, self::SKIP_TABLES, true)) {
                continue;
            }
            if (DB::table($name)->count() === 0) {
                $empty[$name] = true;
            }
        }

        return $empty;
    }

    /** @return array<string, array<int, object>> */
    private function columnsByTable(array $tables): array
    {
        if (! $tables) {
            return [];
        }
        $rows = DB::select(
            "SELECT table_name AS tbl, column_name AS name, data_type AS type,
                    column_type AS full_type, is_nullable AS nullable,
                    column_default AS dflt, extra, character_maximum_length AS len
             FROM information_schema.columns
             WHERE table_schema = ? ORDER BY table_name, ordinal_position", [$this->schema]
        );

        $out = [];
        foreach ($rows as $row) {
            if (in_array($row->tbl, $tables, true)) {
                $out[$row->tbl][] = $row;
            }
        }

        return $out;
    }

    /** @return array<string, array<string, string>> table => [column => referenced table] */
    private function foreignKeysByTable(): array
    {
        $rows = DB::select(
            "SELECT table_name AS tbl, column_name AS col, referenced_table_name AS ref
             FROM information_schema.key_column_usage
             WHERE table_schema = ? AND referenced_table_name IS NOT NULL", [$this->schema]
        );

        $out = [];
        foreach ($rows as $row) {
            $out[$row->tbl][$row->col] = $row->ref;
        }

        return $out;
    }

    /** @return array<string, array<string, true>> table => set of columns in any unique index */
    private function uniqueColumnsByTable(): array
    {
        $rows = DB::select(
            "SELECT table_name AS tbl, column_name AS col
             FROM information_schema.statistics
             WHERE table_schema = ? AND non_unique = 0 AND index_name <> 'PRIMARY'", [$this->schema]
        );

        $out = [];
        foreach ($rows as $row) {
            $out[$row->tbl][$row->col] = true;
        }

        return $out;
    }

    /**
     * Kahn topological sort on FK edges between the given tables.
     * Tables caught in cycles are appended at the end (FK checks are off,
     * and applyFixups() repairs their references afterwards).
     */
    private function topoSort(array $tables, array $fks): array
    {
        $set  = array_fill_keys($tables, true);
        $deps = [];
        foreach ($tables as $table) {
            $deps[$table] = [];
            foreach ($fks[$table] ?? [] as $ref) {
                if ($ref !== $table && isset($set[$ref])) {
                    $deps[$table][$ref] = true;
                }
            }
        }

        $ordered = [];
        while ($deps) {
            $progress = false;
            foreach ($deps as $table => $waiting) {
                if (! $waiting) {
                    $ordered[] = $table;
                    unset($deps[$table]);
                    foreach ($deps as $t => $w) {
                        unset($deps[$t][$table]);
                    }
                    $progress = true;
                }
            }
            if (! $progress) { // cycle — emit remaining in arbitrary order
                foreach (array_keys($deps) as $table) {
                    $ordered[] = $table;
                }
                break;
            }
        }

        return $ordered;
    }

    // ------------------------------------------------------------------
    // Row generation
    // ------------------------------------------------------------------

    private function fillTable(string $table, array $columns, array $fks, array $uniques, Carbon $now): void
    {
        $colNames = array_column($columns, 'name');

        for ($n = 1; $n <= self::ROWS_PER_TABLE; $n++) {
            $row = [];
            foreach ($columns as $col) {
                if (stripos($col->extra, 'auto_increment') !== false
                    || stripos($col->extra, 'GENERATED') !== false) {
                    continue;
                }
                $value = $this->valueFor($table, $col, $fks, $uniques, $colNames, $n, $now);
                if ($value !== ['__omit__']) {
                    $row[$col->name] = $value;
                }
            }
            DB::table($table)->insert($row);
        }

        // cache ids for tables seeded later in the ordering
        if (in_array('id', $colNames, true)) {
            $this->refIds[$table] = DB::table($table)->pluck('id')->all();
        }
    }

    /** @return mixed */
    private function valueFor(string $table, object $col, array $fks, array $uniques, array $colNames, int $n, Carbon $now)
    {
        $name     = $col->name;
        $lower    = strtolower($name);
        $nullable = $col->nullable === 'YES';
        $unique   = isset($uniques[$name]);

        // Foreign keys → an existing (or planned) id in the referenced table.
        if (isset($fks[$name])) {
            $ids = $this->idsOf($fks[$name]);
            if ($ids) {
                return $ids[($n - 1) % count($ids)];
            }
            if ($nullable) {
                return null;
            }
            // referenced table not filled yet (cycle) — placeholder, repaired later
            $this->fixups[] = ['table' => $table, 'column' => $name, 'ref' => $fks[$name]];
            return 1;
        }

        // Polymorphic pairs: xxx_type + xxx_id without a real FK.
        if (Str::endsWith($lower, '_type') && in_array(substr($name, 0, -5) . '_id', $colNames, true)) {
            return 'App\\Models\\User';
        }
        if (Str::endsWith($lower, '_id') && in_array(substr($name, 0, -3) . '_type', $colNames, true)) {
            return 1;
        }

        // Enums: pick a defined value.
        if ($col->type === 'enum') {
            preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $col->full_type, $m);
            $options = $m[1] ?: ['test'];
            return $options[($n - 1) % count($options)];
        }

        // Respect DB defaults for anything we have no strong opinion on —
        // defaults encode the app's own idea of a valid value.
        $interesting = $this->namedValue($table, $lower, $n, $now);
        if ($interesting === ['__none__'] && $col->dflt !== null && ! $unique) {
            return ['__omit__'];
        }

        if ($interesting !== ['__none__']) {
            $value = $interesting;
            if ($unique && is_string($value) && ! Str::contains($value, (string) $n)) {
                $value .= "-{$n}";
            }
            return $value;
        }

        // Fall back on the column type.
        switch ($col->type) {
            case 'tinyint':
                return $col->full_type === 'tinyint(1)' ? ($n % 2) : $n;
            case 'int': case 'bigint': case 'smallint': case 'mediumint':
                return $n;
            case 'decimal': case 'double': case 'float':
                return round(mt_rand(1000, 50000) / 100, 2);
            case 'date':
                return $now->copy()->subDays(mt_rand(0, 300))->toDateString();
            case 'datetime': case 'timestamp':
                return $now->copy()->subDays(mt_rand(0, 300))->toDateTimeString();
            case 'time':
                return sprintf('%02d:00:00', 8 + $n);
            case 'year':
                return (int) $now->format('Y');
            case 'json':
                return '[]';
            case 'text': case 'mediumtext': case 'longtext':
                return "Sample {$name} text for testing ({$table} #{$n}).";
            case 'char':
                return $col->len == 36 ? (string) Str::uuid() : Str::limit("T{$n}", (int) $col->len, '');
            case 'varchar':
                $value = 'Test ' . Str::of($name)->replace('_', ' ')->title() . " {$n}";
                if ($col->len !== null && strlen($value) > (int) $col->len) {
                    $value = Str::limit("T{$n}", (int) $col->len, '');
                }
                return $unique ? Str::limit("{$table}-{$name}-{$n}", max((int) $col->len, 8), '') : $value;
            case 'blob': case 'longblob': case 'mediumblob':
                return '';
            default:
                return $nullable ? null : 0;
        }
    }

    /**
     * Name-based heuristics. Returns ['__none__'] when the name rings no bell.
     * @return mixed
     */
    private function namedValue(string $table, string $lower, int $n, Carbon $now)
    {
        if ($lower === 'created_at' || $lower === 'updated_at') {
            return $now->toDateTimeString();
        }
        if ($lower === 'deleted_at' || Str::endsWith($lower, ['_token', '_secret'])) {
            return ['__none__'];
        }
        if (Str::contains($lower, 'email')) {
            return "test{$n}.{$table}@example.com";
        }
        if (Str::contains($lower, ['phone', 'mobile', 'tel'])) {
            return '06000000' . str_pad((string) $n, 2, '0', STR_PAD_LEFT);
        }
        if ($lower === 'password') {
            return $this->passwordHash ??= Hash::make('test1234');
        }
        if (Str::contains($lower, ['uuid', 'guid'])) {
            return (string) Str::uuid();
        }
        if (Str::contains($lower, ['token', 'secret', 'api_key', 'signature'])) {
            return Str::random(32);
        }
        if (Str::contains($lower, ['url', 'website', 'link', 'domain'])) {
            return 'https://example.com/test';
        }
        if (Str::contains($lower, ['image', 'photo', 'avatar', 'logo', 'picture', 'thumbnail', 'icon'])) {
            return 'no_image.png';
        }
        if (Str::contains($lower, 'color')) {
            return '#3b82f6';
        }
        if ($lower === 'slug' || Str::endsWith($lower, '_slug')) {
            return "{$table}-test-{$n}";
        }
        if (Str::contains($lower, ['ip_address', 'ip'])  && Str::endsWith($lower, ['ip', 'ip_address'])) {
            return '127.0.0.1';
        }
        if (Str::contains($lower, 'user_agent')) {
            return 'TestDataSeeder/1.0';
        }
        if (Str::contains($lower, ['country'])) {
            return 'Morocco';
        }
        if (Str::contains($lower, ['city', 'town'])) {
            return 'Casablanca';
        }
        if (Str::contains($lower, ['address', 'street'])) {
            return "12{$n} Test Street";
        }
        if (Str::contains($lower, ['currency'])) {
            return 'USD';
        }
        if (Str::contains($lower, ['locale', 'language', 'lang'])) {
            return 'en';
        }
        if (Str::contains($lower, ['percent', 'discount', 'tax_rate', 'tax_percentage', 'rate'])) {
            return mt_rand(0, 20);
        }
        if (Str::contains($lower, ['price', 'cost', 'amount', 'total', 'paid', 'due', 'balance', 'salary', 'fee', 'subtotal', 'grandtotal'])) {
            return round(mt_rand(1000, 50000) / 100, 2);
        }
        if (Str::contains($lower, ['quantity', 'qty', 'stock'])) {
            return mt_rand(1, 50);
        }
        if ($lower === 'ref' || Str::startsWith($lower, 'ref_') || Str::endsWith($lower, ['_ref', 'reference', '_no', '_number', 'code', 'sku', 'barcode'])) {
            return strtoupper(Str::limit($table, 6, '')) . '-TST-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
        }
        if (Str::endsWith($lower, ['_name', 'name', 'title', 'label', 'subject'])) {
            return 'Test ' . Str::of($table)->singular()->replace('_', ' ')->title() . " {$n}";
        }
        if (Str::endsWith($lower, ['_at', '_date', 'date'])) {
            return $now->copy()->subDays(mt_rand(0, 300))->toDateTimeString();
        }
        if (Str::contains($lower, ['description', 'note', 'notes', 'comment', 'message', 'body', 'content', 'details', 'remark'])) {
            return "Sample {$lower} generated for testing.";
        }

        return ['__none__'];
    }

    /** ids available in a referenced table, caching lookups */
    private function idsOf(string $table): array
    {
        if (! array_key_exists($table, $this->refIds)) {
            $this->refIds[$table] = DB::table($table)->limit(50)->pluck('id')->all();
        }

        return $this->refIds[$table];
    }

    /** Repair placeholder FK values written inside dependency cycles. */
    private function applyFixups(): void
    {
        foreach ($this->fixups as $fix) {
            $ids = $this->idsOf($fix['ref']);
            if (! $ids) {
                continue;
            }
            DB::table($fix['table'])
                ->whereNotIn($fix['column'], $ids)
                ->update([$fix['column'] => $ids[0]]);
        }
    }
}
