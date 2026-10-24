# Adding Result Publication links to each role's menu

Four different people need a link to reach this feature — an admin (to
review), a teacher (to submit), a student, and a parent. Each lives in a
different sidebar file, so there are four small additions.

## 1. Admin — review submissions

In `resources/views/layouts/menus/partials/exam_module.blade.php` (or
`cms_module.blade.php` if your exam-related links live there instead), add
inside the existing `<div class="menu-sub menu-sub-accordion">` block:

```blade
@if (Route::has('resultpublication.admin.index'))
    <div class="menu-item">
        <a class="menu-link {{ Request::is('admin/results') || Request::is('admin/results/*') ? 'active' : '' }}"
            href="{{ route('resultpublication.admin.index') }}">
            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
            <span class="menu-title">{{ _lang('Result Submissions') }}</span>
        </a>
    </div>
@endif
```

## 2. Teacher — submit results

In `resources/views/layouts/menus/teacher_sidebar.blade.php`, add a menu item
(match whatever markup style the rest of that file already uses — it's the
same `menu-item` / `menu-link` pattern as above):

```blade
@if (Route::has('resultpublication.teacher.index'))
    <div class="menu-item">
        <a class="menu-link {{ Request::is('teacher/results') || Request::is('teacher/results/*') ? 'active' : '' }}"
            href="{{ route('resultpublication.teacher.index') }}">
            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
            <span class="menu-title">{{ _lang('Submit Result') }}</span>
        </a>
    </div>
@endif
```

## 3. Student — view their results

In `resources/views/layouts/menus/student_sidebar.blade.php`:

```blade
@if (Route::has('resultpublication.student.index'))
    <div class="menu-item">
        <a class="menu-link {{ Request::is('student/my-results') ? 'active' : '' }}"
            href="{{ route('resultpublication.student.index') }}">
            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
            <span class="menu-title">{{ _lang('My Results') }}</span>
        </a>
    </div>
@endif
```

## 4. Parent — view their children's results

In `resources/views/layouts/menus/parent_sidebar.blade.php`:

```blade
@if (Route::has('resultpublication.parent.index'))
    <div class="menu-item">
        <a class="menu-link {{ Request::is('parent/child-results') ? 'active' : '' }}"
            href="{{ route('resultpublication.parent.index') }}">
            <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
            <span class="menu-title">{{ _lang("My Children's Results") }}</span>
        </a>
    </div>
@endif
```

Every snippet is wrapped in `@if (Route::has(...))`, so if you paste one into
the wrong file or before running migrations, it just won't render — it won't
break the page (same lesson learned from the dashboard outage earlier in this
project).
