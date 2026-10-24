@extends('install.layouts.master')

@section('title', _lang('start_install'))

@section('container')
    <div class="install-start-container">
        <h1 class="header_one text-center mb-4">Mighty School Software Installation</h1>
        <hr>

        <p class="paragraph_justify">
            Please proceed step by step with proper data according to the instructions.
        </p>
        <p class="paragraph_justify">
            Before starting the installation process, please collect the following information.
            Without this information, you won’t be able to complete the installation.
        </p>

        <div class="info-box mb-4">
            <h5 class="mb-2">Required Information <span class="text-danger">*</span></h5>
            <ul class="info-box_ul">
                <li><strong>Host Name</strong> – e.g., 127.0.0.1 or your server IP</li>
                <li><strong>Database Name</strong> – The name of the MySQL database</li>
                <li><strong>Database Username</strong> – MySQL user with privileges</li>
                <li><strong>Database Password</strong> – Password for the above user</li>
            </ul>
        </div>

        <div class="d-flex justify-content-end">
            <a href="{{ url('install/requirements') }}" class="btn btn-primary">
                {{ _lang('install_next') }}
            </a>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .install-start-container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .header_one {
            font-size: 2rem;
            font-weight: 700;
        }

        .paragraph_justify {
            text-align: justify;
            margin-bottom: 15px;
        }

        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
        }

        .info-box_ul {
            list-style-type: disc;
            padding-left: 20px;
            margin: 0;
        }

        .info-box_ul li {
            margin-bottom: 8px;
        }
    </style>
@endpush
