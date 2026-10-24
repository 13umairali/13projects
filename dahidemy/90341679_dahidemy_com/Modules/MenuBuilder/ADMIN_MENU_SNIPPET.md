# Adding "Website Menu" to the admin sidebar

Same approach as the Dynamic Forms link — a small, surgical addition to
`resources/views/layouts/menus/partials/cms_module.blade.php` rather than
replacing that whole file.

## 1. Add to the active-state check

Near the top of the file, in the `Request::is(...) || ...` chain, add:

```php
Request::is('custom-menu') ||
Request::is('custom-menu/*') ||
```

## 2. Add the menu link

Inside the `<div class="menu-sub menu-sub-accordion">` block, add:

```blade
@if (Route::has('menubuilder.index'))
    <div class="menu-item">
        <a class="menu-link {{ Request::is('custom-menu') || Request::is('custom-menu/*') ? 'active' : '' }}"
            href="{{ route('menubuilder.index') }}">
            <span class="menu-bullet">
                <span class="bullet bullet-dot"></span>
            </span>
            <span class="menu-title">{{ _lang('Website Menu') }}</span>
        </a>
    </div>
@endif
```

The `@if (Route::has(...))` wrapper means this is safe to add even before the
module is installed — it just won't render until the route actually exists.
