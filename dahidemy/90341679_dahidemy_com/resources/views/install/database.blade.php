@extends('install.layouts.master')

@section('title', _lang('install_database'))

@section('container')
    <form id="db-form" method="POST">
        @csrf

        <div id="db-errors" class="alert alert-danger d-none"></div>

        <div class="mb-3">
            <label for="software_name" class="form-label">{{ _lang('Software Name') }}</label>
            <input type="text" name="software_name" id="software_name" class="form-control"
                value="{{ old('software_name') }}" placeholder="Software Name" required>
        </div>

        <div class="mb-3">
            <label for="host" class="form-label">{{ _lang('install_host') }}</label>
            <input type="text" name="host" id="host" class="form-control" value="127.0.0.1" required>
        </div>

        <div class="mb-3">
            <label for="username" class="form-label">{{ _lang('install_username') }}</label>
            <input type="text" name="username" id="username" class="form-control"
                value="{{ old('username') ?? 'root' }}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ _lang('install_password') }}</label>
            <input type="password" name="password" id="password" class="form-control" value="{{ old('password') }}">
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">{{ _lang('install_name') }}</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
        </div>

        <div class="mb-3">
            <label for="port" class="form-label">{{ _lang('install_port') }}</label>
            <input type="number" name="port" id="port" class="form-control" value="3306" required>
        </div>

        <div class="d-flex justify-content-end mt-3">
            <button type="button" id="check-db" class="btn btn-info me-2">{{ _lang('Check Connection') }}</button>
            <button type="submit" id="next-button" class="btn btn-primary" disabled>{{ _lang('install_next') }}</button>
        </div>
    </form>
@endsection


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        function showError(message) {
            $('#db-errors').removeClass('d-none').html(message);
            $('#next-button').prop('disabled', true);
        }

        function showSuccess() {
            $('#db-errors').addClass('d-none').html('');
            $('#next-button').prop('disabled', false);
        }

        // Step 1: Check DB connection
        $('#check-db').on('click', function() {
            const data = {
                _token: '{{ csrf_token() }}',
                host: $('#host').val(),
                username: $('#username').val(),
                password: $('#password').val(),
                name: $('#name').val(),
                port: $('#port').val()
            };

            $('#check-db').text('Checking...').prop('disabled', true);

            $.ajax({
                url: "{{ route('install.check_database') }}",
                method: 'POST',
                data: data,
                success: function(res) {
                    if (res.status === 'success') {
                        showSuccess();
                        alert('Database connection successful. You can proceed.');
                    } else {
                        showError(res.message || 'Database connection failed.');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Server error occurred.';
                    showError(msg);
                },
                complete: function() {
                    $('#check-db').text('{{ _lang('Check Connection') }}').prop('disabled',
                        false);
                }
            });
        });

        // Step 2: Save DB credentials & go to next step
        $('#db-form').on('submit', function(e) {
            e.preventDefault();

            if ($('#next-button').prop('disabled')) {
                alert('Please check database connection first.');
                return;
            }

            const data = {
                _token: '{{ csrf_token() }}',
                software_name: $('#software_name').val(),
                host: $('#host').val(),
                username: $('#username').val(),
                password: $('#password').val(),
                name: $('#name').val(),
                port: $('#port').val()
            };

            $('#next-button').text('Saving...').prop('disabled', true);

            $.ajax({
                url: "{{ route('install.database.save') }}",
                method: 'POST',
                data: data,
                success: function(res) {
                    if (res.status === 'success') {
                        window.location.href = res.redirect; // Go to installation
                    } else {
                        showError(res.message || 'Failed to save database credentials.');
                        $('#next-button').prop('disabled', false).text(
                            '{{ _lang('install_next') }}');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Server error occurred.';
                    showError(msg);
                    $('#next-button').prop('disabled', false).text(
                        '{{ _lang('install_next') }}');
                }
            });
        });
    });
</script>
