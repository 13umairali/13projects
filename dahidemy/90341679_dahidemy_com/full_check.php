<?php

/**
 * Usage: upload to project root, then run:
 *   /usr/local/lsws/lsphp83/bin/lsphp full_check.php YOUR-DOMAIN.com
 *
 * Example:
 *   /usr/local/lsws/lsphp83/bin/lsphp full_check.php abc.dahidemy.com
 *
 * Shows, in one shot: what that domain resolves to, every institute in the
 * system, and every course/event/form with its real institute_id and
 * status - so a mismatch is immediately visible without more back-and-forth.
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$domain = $argv[1] ?? null;
if (! $domain) {
    echo "Usage: lsphp full_check.php YOUR-DOMAIN.com\n";
    exit(1);
}

$request = Illuminate\Http\Request::create('http://'.$domain.'/', 'GET');
$request->headers->set('Host', $domain);
app()->instance('request', $request);

echo str_repeat('=', 60)."\n";
echo "1. DOMAIN RESOLUTION\n";
echo str_repeat('=', 60)."\n";
echo "Simulated domain: {$domain}\n";

$institute = \App\Helpers\InstituteHelper::resolveInstituteFromRequest($request);

if (! $institute) {
    echo "Result: NO institute resolved at all (null).\n";
} else {
    echo "==> This domain resolves to: Institute #{$institute->id} \"{$institute->name}\" (domain on file: ".($institute->domain ?: '(none)').")\n";
}

echo PHP_EOL;
echo str_repeat('=', 60)."\n";
echo "2. EVERY INSTITUTE IN THE SYSTEM\n";
echo str_repeat('=', 60)."\n";
foreach (\Modules\Authentication\Models\Institute::withTrashed()->get(['id', 'name', 'domain', 'status', 'deleted_at']) as $i) {
    $flags = [];
    if ($i->deleted_at) {
        $flags[] = 'DELETED';
    }
    if (! $i->status) {
        $flags[] = 'INACTIVE';
    }
    $flagText = $flags ? ' ['.implode(', ', $flags).']' : '';
    echo "  #{$i->id} \"{$i->name}\" -> domain: ".($i->domain ?: '(none)')."{$flagText}\n";
}

echo PHP_EOL;
echo str_repeat('=', 60)."\n";
echo "3. EVERY COURSE (with its real institute_id)\n";
echo str_repeat('=', 60)."\n";
if (class_exists(\Modules\Elearning\Models\Course::class)) {
    $courses = \Modules\Elearning\Models\Course::withTrashed()->get(['id', 'title', 'institute_id', 'status']);
    if ($courses->isEmpty()) {
        echo "  (no course rows exist at all in the database)\n";
    }
    foreach ($courses as $c) {
        echo "  #{$c->id} \"{$c->title}\" -> institute_id: {$c->institute_id}, status: {$c->status}\n";
    }
} else {
    echo "  Elearning module not found.\n";
}

echo PHP_EOL;
echo str_repeat('=', 60)."\n";
echo "4. EVERY EVENT (with its real institute_id)\n";
echo str_repeat('=', 60)."\n";
if (class_exists(\Modules\Academic\Models\Event::class)) {
    $events = \Modules\Academic\Models\Event::all(['id', 'name', 'institute_id']);
    if ($events->isEmpty()) {
        echo "  (no event rows exist at all in the database)\n";
    }
    foreach ($events as $e) {
        echo "  #{$e->id} \"{$e->name}\" -> institute_id: {$e->institute_id}\n";
    }
} else {
    echo "  Academic Event model not found.\n";
}

echo PHP_EOL;
echo str_repeat('=', 60)."\n";
echo "5. EVERY DYNAMIC FORM (with its real institute_id)\n";
echo str_repeat('=', 60)."\n";
if (class_exists(\Modules\FormBuilder\Models\DynamicForm::class)) {
    $forms = \Modules\FormBuilder\Models\DynamicForm::all(['id', 'name', 'institute_id', 'status']);
    if ($forms->isEmpty()) {
        echo "  (no form rows exist at all in the database)\n";
    }
    foreach ($forms as $f) {
        echo "  #{$f->id} \"{$f->name}\" -> institute_id: {$f->institute_id}, status: {$f->status}\n";
    }
} else {
    echo "  FormBuilder module not found.\n";
}

echo PHP_EOL;
echo str_repeat('=', 60)."\n";
echo "DONE - compare the institute_id in sections 3/4/5 against the\n";
echo "resolved institute # from section 1. If they don't match, that's\n";
echo "the entire problem: content was created under a different\n";
echo "institute than the one this domain actually resolves to.\n";
echo str_repeat('=', 60)."\n";