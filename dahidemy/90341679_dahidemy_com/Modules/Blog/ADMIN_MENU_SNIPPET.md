# Adding "Blog" to the admin sidebar

Same top-level pattern as Courses (this is content management, not part of
the CMS accordion). Add to `resources/views/layouts/menus/sidebar.blade.php`:

```blade
@if (Route::has('blog.admin.posts.index'))
    <div data-kt-menu-trigger="click"
        class="menu-item menu-accordion {{ Request::is('admin/blog/*') ? 'here show' : '' }}">

        <span class="menu-link">
            <span class="menu-icon">
                <i class="fa fa-newspaper fs-3"></i>
            </span>
            <span class="menu-title">{{ _lang('Blog') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion">
            <div class="menu-item">
                <a class="menu-link {{ Request::is('admin/blog/posts') || Request::is('admin/blog/posts/*') ? 'active' : '' }}"
                    href="{{ route('blog.admin.posts.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('All Posts') }}</span>
                </a>
            </div>
            <div class="menu-item">
                <a class="menu-link {{ Request::is('admin/blog/categories') || Request::is('admin/blog/categories/*') ? 'active' : '' }}"
                    href="{{ route('blog.admin.categories.index') }}">
                    <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                    <span class="menu-title">{{ _lang('Categories') }}</span>
                </a>
            </div>
        </div>
    </div>
@endif
```

## About the "Content" field / rich text editor

The post content field is a plain `<textarea>` — I didn't wire up a specific
WYSIWYG editor since I don't know which one (if any) your admin panel
already loads globally for other rich-text fields (About Us, Course
descriptions, etc.). If your panel already has one available, it likely
auto-applies to any `<textarea>` with a specific class — check what class
name an existing rich-text field uses elsewhere in your admin panel (e.g.
inspect the About Us edit page) and add that same class to
`Modules/Blog/resources/views/admin/posts/form.blade.php`'s `#postContent`
textarea.
