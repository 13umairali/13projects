@extends('install.layouts.master')

@section('title', _lang('install_requirements'))

@section('container')
    <form action="{{ url('install/database') }}" method="GET" id="install-form">
        @csrf

        <div class="form-group">
            <label for="username">{{ _lang('Envato Username') }}</label>
            <input type="text" name="username" id="username" class="form-control" value="{{ old('username') }}"
                placeholder="Envato username" required>
        </div>

        <div class="form-group">
            <label for="purchase_key">{{ _lang('Purchase Key') }}</label>
            <input type="text" name="purchase_key" id="purchase_key" class="form-control"
                value="{{ old('purchase_key') }}" placeholder="Envato Item Purchase Code" required>
            <span id="purchase_key-error" class="text-danger d-block mt-1"></span>

            <div id="purchase_key-success" class="form-check mt-2 d-none">
                <input class="form-check-input" type="checkbox" disabled checked>
                <label class="form-check-label">{{ _lang('Purchase key is valid.') }}</label>
            </div>

            <button type="button" id="check-key" class="btn btn-sm btn-info mt-2" style="margin-top: 10px;">
                {{ _lang('Check Purchase Key') }}
            </button>
        </div>

        <div class="form-group text-right">
            <button type="submit" id="next-button" class="btn btn-primary" style="display: none;" disabled>
                {{ _lang('install_next') }}
            </button>
        </div>
    </form>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        function resetValidationState() {
            $('#purchase_key').removeClass('is-valid is-invalid').prop('readonly', false);
            $('#purchase_key-success').addClass('d-none');
            $('#purchase_key-error').text('');
            $('#next-button').hide().prop('disabled', true);
            $('#check-key').show().prop('disabled', false).text('Check Purchase Key');
        }

        function showError(message) {
            $('#purchase_key-error').text(message).show();
            $('#purchase_key').addClass('is-invalid').removeClass('is-valid');
            $('#purchase_key-success').addClass('d-none');
            $('#next-button').hide().prop('disabled', true);
        }

        function showSuccess() {
            $('#purchase_key-error').text('');
            $('#purchase_key').removeClass('is-invalid').addClass('is-valid').prop('readonly', true);
            $('#purchase_key-success').removeClass('d-none');
            $('#check-key').hide();
            $('#next-button').show().prop('disabled', false);
        }

        $('#check-key').on('click', function() {
            const purchase_key = $('#purchase_key').val().trim();
            const username = $('#username').val().trim();

            if (!purchase_key || purchase_key.length !== 36) {
                showError('Purchase key must be exactly 36 characters.');
                return;
            }

            if (!username) {
                showError('Envato username is required.');
                return;
            }

            $('#purchase_key-error').text('');
            $('#check-key').text('Checking...').prop('disabled', true);

            $.ajax({
                url: "{{ route('install.validate') }}",
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    purchase_key,
                    username
                },
                success: function(response) {
                    if (response.status === 'success') {
                        showSuccess();
                    } else {
                        showError(response.message || 'Invalid purchase key.');
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Server error occurred.';
                    showError(msg);
                },
                complete: function() {
                    $('#check-key').text('Check Purchase Key').prop('disabled', false);
                }
            });
        });

        // Re-check logic if user edits the key after validation
        $('#purchase_key').on('input', function() {
            resetValidationState();
        });

        // Prevent form submission if not validated
        $('#install-form').on('submit', function(e) {
            if ($('#next-button').prop('disabled')) {
                e.preventDefault();
                alert('Please validate your purchase key first.');
            }
        });

    });
</script>
