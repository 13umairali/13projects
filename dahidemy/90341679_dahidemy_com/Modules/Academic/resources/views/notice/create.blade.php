@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Notice') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('notices.index') }}">
        {{ _lang('Notice List') }}
    </a>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <form method="post" action="{{ route('notices.store') }}" enctype="multipart/form-data" data-disable-on-submit>
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Type') }}</label>
                            <select class="form-select form-select-sm form-select-solid" name="type" id="notice_type_select">
                                @foreach (\Modules\Academic\Http\Controllers\WEB\NoticeController::TYPES as $type)
                                    <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>{{ _lang($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6" id="display_on_website_field">
                        <div class="form-group">
                            <label class="control-label my-2">&nbsp;</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="display_on_website" value="1" id="displayOnWebsite"
                                    {{ old('display_on_website') ? 'checked' : '' }}>
                                <label class="form-check-label" for="displayOnWebsite">{{ _lang('Display on Website') }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Title') }}</label>
                            <input type="text" class="form-control" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Notice') }}</label>
                            <div class="form-text mb-2">
                                {{ _lang('Dynamic tags available: {{School Name}}, {{Date}}, and — when a recipient staff member is selected below — {{Staff Name}}, {{Designation}}.') }}
                            </div>
                            <textarea class="form-control summernote" name="notice" required>{{ old('notice') }}</textarea>
                            @error('notice')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row" id="user_type_field">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('User Type') }}</label>
                            <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                name="user_type[]" multiple>
                                @foreach ($roles as $role)
                                    @if (!in_array(strtolower($role->name), ['superadmin', 'owner']))
                                        <option value="{{ $role->name }}">
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>

                            @error('user_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror

                        </div>
                    </div>
                </div>

                <div class="row" id="recipient_staff_field" style="display:none;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Recipient Staff (for Appointment/Dismissal Letters)') }}</label>
                            <select class="form-select form-select-sm form-select-solid" data-control="select2"
                                name="recipient_staff_id">
                                <option value="">{{ _lang('Select Staff') }}</option>
                                @foreach ($staffs as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <script>
                    (function() {
                        var typeSelect = document.getElementById('notice_type_select');
                        var userTypeField = document.getElementById('user_type_field');
                        var recipientField = document.getElementById('recipient_staff_field');

                        function toggle() {
                            var isLetter = ['Appointment Letter', 'Dismissal Letter'].includes(typeSelect.value);
                            userTypeField.style.display = isLetter ? 'none' : '';
                            recipientField.style.display = isLetter ? '' : 'none';
                        }

                        typeSelect.addEventListener('change', toggle);
                        toggle();
                    })();
                </script>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label class="control-label my-2">{{ _lang('Image') }}</label>
                            <input type="file" class="form-control" name="image">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mt-5">
                        <div class="form-group">
                            <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
                            <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection
