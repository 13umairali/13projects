{{-- @if ($user->can('google_meet_panel') || $user->can('google_meet_index')) --}}
{{--
    "Create Meeting" used to be a second, separate sidebar entry pointing at
    google-meet.create. That page and the "All Meetings" list both do the same
    job (the list already has its own "Create Meeting" button in the
    page header), so the duplicate entry has been removed. This is now a
    single, plain menu item — no accordion needed for one destination.
--}}
<!--begin:Menu item-->
<div class="menu-item">
    <a class="menu-link {{ Request::is('google-meet*') ? 'active' : '' }}"
        href="{{ route('google-meet.index') }}">
        <span class="menu-icon">
            <!-- Google Meet Icon -->
            <i class="fa-solid fa-video fs-2"></i>
        </span>
        <span class="menu-title">
            {{ _lang('Google Meet') }}
        </span>
    </a>
</div>
<!--end:Menu item-->
{{-- @endif --}}
