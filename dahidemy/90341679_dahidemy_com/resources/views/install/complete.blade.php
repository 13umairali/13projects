@extends('install.layouts.master')

@section('title', _lang('install_complete'))

@section('container')
    <div class="text-center mb-4">
        <h3>{{ _lang('Installation Completed Successfully!') }}</h3>
        <p class="paragraph mb-4">
            You can login now with the default credentials.
            <br>Email: <strong>superadmin@gmail.com</strong>
            <br>Password: <strong>12345678</strong>
        </p>

        <!-- 🖥️ Buttons -->
        <div class="d-flex justify-content-center gap-2 mb-4 flex-wrap">
            <!-- Root Page / Frontend -->
            <a href="{{ url('/login') }}" class="btn btn-primary btn-lg" style="margin-bottom: 10px;">
                <i class="fa fa-home me-1"></i> {{ _lang('Login Page') }}
            </a>

            <!-- API / DB Status Check -->
            <button id="check-api" class="btn btn-success btn-lg" style="margin-bottom: 10px;">
                <i class="fa fa-server me-1"></i> {{ _lang('Check API / DB Status') }}
            </button>
        </div>

        <!-- API Status Result -->
        <div id="api-status" class="mt-3"></div>
    </div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#check-api').on('click', function() {
            const $btn = $(this);
            $btn.prop('disabled', true).text('{{ _lang('Checking...') }}');
            $('#api-status').html('');

            $.ajax({
                url: 'https://{{ request()->getHost() }}/api/check-status',
                method: 'GET', // GET request does not need CSRF
                success: function(res) {
                    let html = '';
                    if (res.status) {
                        html =
                            `<div class="alert alert-success">{{ _lang('API & Database are working perfectly!') }}</div>`;
                    } else {
                        html =
                            `<div class="alert alert-danger">{{ _lang('Something went wrong with API or Database.') }}</div>`;
                    }
                    $('#api-status').html(html);
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message ||
                        '{{ _lang('Server error occurred!') }}';
                    $('#api-status').html(`<div class="alert alert-danger">${msg}</div>`);
                },
                complete: function() {
                    $btn.prop('disabled', false).text(
                        '{{ _lang('Check API / DB Status') }}');
                }
            });
        });
    });
</script>
