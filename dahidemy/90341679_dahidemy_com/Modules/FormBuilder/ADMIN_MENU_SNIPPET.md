# Adding "Dynamic Forms" to the admin sidebar

The sidebar (`resources/views/layouts/menus/sidebar.blade.php`) is a single 58KB
file that `@include`s smaller partials per module — the CMS-related links (Pages,
Banners, FAQs, Testimonials, etc.) live in
`resources/views/layouts/menus/partials/cms_module.blade.php`.

Rather than replace that whole file (risky — it's hand-tuned and large), make these
two small, surgical edits to it:

## 1. Add "dynamic-forms" to the accordion's active-state check

Near the top of the file, find this block:

```php
class="menu-item menu-accordion {{ Request::is('admission-forms') ||
Request::is('admission-forms/*') ||
...
Request::is('institute-image-settings') ||
Request::is('institute-image-settings/*')
    ? 'here show'
    : '' }}">
```

Add two more lines right before the closing `Request::is('institute-image-settings/*')`
line (order doesn't matter, just keep the `||` chain intact):

```php
Request::is('dynamic-forms') ||
Request::is('dynamic-forms/*') ||
```

## 2. Add the menu link itself

Inside the same file, find the `<div class="menu-sub menu-sub-accordion">` block —
it contains one `<div class="menu-item">...</div>` per link (Admission Forms, Pages,
Banners, About Us, FAQs, Gallery Images, etc.). Paste this new item anywhere in that
list, e.g. right after the "Testimonials" item:

```blade
<!--begin:Menu item-->
<div class="menu-item">
    <!--begin:Menu link-->
    <a class="menu-link {{ Request::is('dynamic-forms') || Request::is('dynamic-forms/*') ? 'active' : '' }}"
        href="{{ route('formbuilder.forms.index') }}">
        <span class="menu-bullet">
            <span class="bullet bullet-dot"></span>
        </span>
        <span class="menu-title">{{ _lang('Dynamic Forms') }}</span>
    </a>
    <!--end:Menu link-->
</div>
<!--end:Menu item-->
```

That's it — no other file needs to change for the menu link to appear and highlight
correctly when an admin is on any `/dynamic-forms*` page.
