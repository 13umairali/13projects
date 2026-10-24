# Adding "Website Popups" to the admin sidebar

Same pattern as before — add to `resources/views/layouts/menus/partials/cms_module.blade.php`.

## 1. Active-state check

```php
Request::is('popups') ||
Request::is('popups/*') ||
```

## 2. Menu link

```blade
@if (Route::has('popupbroadcaster.index'))
    <div class="menu-item">
        <a class="menu-link {{ Request::is('popups') || Request::is('popups/*') ? 'active' : '' }}"
            href="{{ route('popupbroadcaster.index') }}">
            <span class="menu-bullet">
                <span class="bullet bullet-dot"></span>
            </span>
            <span class="menu-title">{{ _lang('Website Popups') }}</span>
        </a>
    </div>
@endif
```
