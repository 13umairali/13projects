# Adding "Courses" to the admin sidebar

This is a new admin section, so it works best as its own top-level menu item
rather than folding into an existing accordion. Add this wherever your
sidebar defines top-level sections in `resources/views/layouts/menus/sidebar.blade.php`
(look for a similar `<div class="menu-item menu-accordion">` block to place
it next to):

```blade
@if (Route::has('elearning.admin.courses.index'))
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('admin/courses') ||
        Request::is('admin/courses/*') ||
        Request::is('admin/course-categories') ||
        Request::is('admin/course-categories/*')
            ? 'here show'
            : '' }}">

        <span class="menu-link">
            <span class="menu-icon">
                <i class="fa fa-graduation-cap fs-3"></i>
            </span>
            <span class="menu-title">{{ _lang('Courses') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion">
            <div class="menu-item">
                <a class="menu-link {{ Request::is('admin/courses') || Request::is('admin/courses/*') ? 'active' : '' }}"
                    href="{{ route('elearning.admin.courses.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('All Courses') }}</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ Request::is('admin/course-categories') || Request::is('admin/course-categories/*') ? 'active' : '' }}"
                    href="{{ route('elearning.admin.categories.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Course Categories') }}</span>
                </a>
            </div>
        </div>
    </div>
@endif
```

Same convention as everything else added in this project: wrapped in
`@if (Route::has(...))`, so it's safe to paste even before you've replaced
the underlying files — it just won't render until the route actually exists.

## What this does and doesn't cover

**Covered**: creating/editing courses (title, category, pricing, intro
video, description, image, status), chapters, lessons (title + video
link), course features, and course FAQs.

**Not covered**: creating quizzes or questions. `Modules\QuestionBank` is
*also* 100% API-only with zero web presence — bringing that to the web
would be a genuinely separate, large project (it has its own deep taxonomy:
boards, topics, difficulty levels, question types, etc.). A chapter's quiz
relationship (`Chapter::quizzes()`) still works if a quiz was created via
the API/mobile app — it just can't be created from this web builder yet.

Also not covered: Zoom meeting scheduling, class routines, and per-student
drip-content visibility scheduling (`ContentVisibility`) — all API-only
still, same as before.
