@extends('install.layouts.master')

@section('title', _lang('install_installation'))
<style>
    .welcome-message {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 5px;
    }

    .upgrade-highlights {
        list-style-type: disc;
        padding-left: 20px;
    }

    .upgrade-highlights li {
        margin: 5px 0;
    }
</style>

@section('container')
    <div class="welcome-message">
        <h1>{{ _lang('install_welcome') }}</h1>
        <p class="paragraph">{{ _lang('install_msg') }}</p>
        <p class="paragraph">
            Install Upgrade Notice
            <strong>Install New Features</strong>
            Install Enjoy Improvements
        </p>
        <ul class="upgrade-highlights">
            <li>
                {{ _lang('School Management System') }}
            </li>
            <li>
                {{ _lang('Staff Management System') }}
            </li>
            <li>
                {{ _lang('Accounts & Financial Management') }}
            </li>
            <li>
                {{ _lang('Authentication & Role-Based Permissions') }}
            </li>
            <li>
                {{ _lang('Global Settings & Configurations') }}
            </li>
            <li>
                {{ _lang('Notifications & SMS Module') }}
            </li>
            <li>
                {{ _lang('HRM Management') }}
            </li>
            <li>
                {{ _lang('Reports') }}
            </li>
        </ul>
    </div>

    <form action="{{ url('install/installation') }}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <button type="submit" class="btn pull-right"> {{ _lang('install_install') }}</button>
        </div>
    </form>
@endsection
