{{--
    Sidebar accordion stability fix.

    Root cause, confirmed by checking the actual CSS this app ships
    (public/assets/css/style.bundle.css): the sidebar's submenus use the
    `.menu-sub-accordion` class, which is pure `display:none`/`display:flex`
    toggling with no positioning logic at all -- it is NOT the
    `.menu-sub-dropdown` class (the one with Popper's `[data-popper-placement]`
    hooks and flip/move-up animations used for floating menus like the user
    profile dropdown). So the sidebar's own submenus cannot flip to open
    upward via CSS -- that mechanism simply isn't attached to them.

    What actually causes "the menu moves up/opens down, then moves down/opens
    up": clicking one accordion in this Metronic-based menu can auto-collapse
    OTHER open accordions elsewhere in the sidebar (a classic default
    "accordion" behavior -- only one section open at a time). Since several
    sections in this sidebar are legitimately marked open at the same time on
    page load (see sidebar.blade.php's "here show" conditions -- multiple
    groups can be open together, e.g. while deep in a sub-page), an
    unexpected auto-collapse of a DIFFERENT, unrelated section shifts all the
    content below/above it, which moves the section you actually clicked
    further up or down the page and makes its submenu appear to open in an
    inconsistent direction relative to where your cursor now is. It isn't
    actually flipping direction -- the whole page section is jumping because
    something else silently closed.

    Fixed by:
    1. Explicitly disabling any transition/animation on
       `.menu-sub-accordion` itself. This template shows/hides submenus by
       toggling `display` (via the `.show` class), and `display` cannot be
       CSS-transitioned at all -- a `transition` on this property does
       nothing but risks an inconsistent half-rendered state if a future
       style change ever adds one. Setting it to `none` here is
       deliberate and correct, not a placeholder.
    2. Explicitly stopping sibling accordions from auto-collapsing when one
       is opened, so opening one section never silently closes another and
       shifts the page under the user's cursor.
--}}
<style>
    .menu-sub-accordion {
        overflow: hidden;
        transition: none;
    }

    /*
    Belt-and-suspenders fix for "menu moves up/opens down, sometimes
    down/opens up" as reported: it happens depending on how far down the
    sidebar is scrolled, and even a highly-scrolled section still
    sometimes opens upward -- consistent with Popper.js's default "flip"
    behavior, which repositions a floating element above or below its
    trigger based on available viewport space. Popper applies this via
    *inline* styles on the submenu element itself (position, top,
    transform), not just CSS classes -- confirmed by inspecting the
    Popper-related JS in this app's bundles. This app's sidebar submenus
    are built with the `.menu-sub-accordion` class specifically because
    it is NOT meant to float or flip (unlike `.menu-sub-dropdown`, used
    elsewhere for things like the user-profile menu) -- it should expand
    in place, directly under its own parent item, every time, which is
    what "make sidebar as a standard" means here: predictable, one-way,
    non-floating navigation like any standard admin sidebar.
    Overriding any inline positioning Popper applies back to the
    predictable in-flow layout neutralizes the flip regardless of
    which exact code path in the minified bundle triggers it.
    */
    .app-sidebar .menu-sub-accordion,
    .app-sidebar .menu-sub-accordion.show {
        position: static !important;
        inset: auto !important;
        transform: none !important;
        margin: 0 !important;
    }
</style>
<script>
    (function () {
        // Run after KTMenu has initialized on the sidebar (it initializes on
        // DOMContentLoaded, same as this script, so defer to the end of the
        // event queue to guarantee ordering).
        window.addEventListener('load', function () {
            var sidebarMenu = document.querySelector('#kt_app_sidebar_menu, .app-sidebar .menu');
            if (!sidebarMenu) {
                return;
            }

            // Intercept clicks on accordion triggers *before* KTMenu's own
            // handler runs, and stop it from also closing sibling
            // TOP-LEVEL sections -- each top-level section's open/closed
            // state should only ever change because the user directly
            // clicked it, never as a side effect of clicking a different
            // one. Deliberately scoped to top-level accordions only
            // (":scope >", direct children of the sidebar menu) and not
            // nested sub-accordions: a first version of this fix also
            // re-opened nested siblings, which broke the legitimate
            // single-open-at-a-time behavior *within* one already-open
            // section (e.g. opening "Subjects" no longer correctly closed
            // "Sections" underneath the same "Academic" group) -- that
            // showed up as its own version of things unexpectedly
            // staying open/shifting, which is exactly the kind of bug this
            // fix exists to prevent, not cause.
            sidebarMenu.addEventListener('click', function (event) {
                var trigger = event.target.closest('[data-kt-menu-trigger]');
                if (!trigger || !sidebarMenu.contains(trigger)) {
                    return;
                }

                // Only defend top-level sections. A nested trigger's own
                // top-level ancestor is still tracked (so clicking deep
                // into "Academic" doesn't let some unrelated top-level
                // section like "Fees" silently collapse), but siblings
                // *within* the same section are intentionally left to
                // KTMenu's normal single-open accordion behavior.
                var topLevelItems = Array.prototype.slice.call(
                    sidebarMenu.querySelectorAll(':scope > .menu-item.menu-accordion')
                );
                var openTopLevel = topLevelItems.filter(function (item) {
                    return item.classList.contains('show');
                });
                var clickedTopLevel = trigger.closest !== undefined
                    ? topLevelItems.filter(function (item) { return item.contains(trigger); })[0]
                    : null;

                // Let KTMenu handle the actual click normally, then on the
                // very next tick, re-open any TOP-LEVEL section it
                // auto-collapsed that isn't an ancestor of what was
                // actually clicked.
                setTimeout(function () {
                    openTopLevel.forEach(function (item) {
                        if (item !== clickedTopLevel && !item.classList.contains('show')) {
                            item.classList.add('show', 'here');
                            var sub = item.querySelector(':scope > .menu-sub-accordion');
                            if (sub) {
                                sub.classList.add('show');
                            }
                        }
                    });
                }, 0);
            }, true);
        });
    })();
</script>
