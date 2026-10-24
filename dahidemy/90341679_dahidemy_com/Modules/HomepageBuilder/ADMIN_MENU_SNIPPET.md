# Adding "Homepage Sections" to the admin sidebar

Add to `resources/views/layouts/menus/sidebar.blade.php`:

```blade
@if (Route::has('homepagebuilder.admin.sections.index'))
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('admin/homepage/*') ? 'here show' : '' }}">

        <span class="menu-link">
            <span class="menu-icon">
                <i class="fa fa-object-group fs-3"></i>
            </span>
            <span class="menu-title">{{ _lang('Homepage Builder') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion">
            <div class="menu-item">
                <a class="menu-link {{ Request::is('admin/homepage/sections') ? 'active' : '' }}"
                    href="{{ route('homepagebuilder.admin.sections.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Section Order') }}</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ Request::is('admin/homepage/director-message') ? 'active' : '' }}"
                    href="{{ route('homepagebuilder.admin.director-message.edit') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang("Director's Message") }}</span>
                </a>
            </div>
        </div>
    </div>
@endif
```

## How this works

- The first time an admin visits **Section Order**, the system auto-creates a
  row for every available section (About, Stats, Events, Blog, etc.) in the
  current default order, all turned on — so nothing changes on your live
  site until an admin actually touches a toggle.
- If a future update adds a new section type, it gets appended automatically
  the next time that admin visits the page — nothing gets silently hidden by
  an upgrade.
- Turning a section "on" here doesn't force it to show if it has no content
  (e.g. Events stays hidden with zero events) — this only controls whether
  it's *allowed* to show and where it sits in the order.
