@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Subject Config') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <form method="get" autocomplete="off">
                @csrf

                <div class="row">
                    <div class="col-sm-12 col-lg-6">
                        <div class="form-group">
                            <label class="mb-2 control-label" for="class_id_select">{{ _lang('Class') }}</label>
                            <select name="class_id" id="class_id_select" class="form-select form-select-sm form-select-solid"
                                data-control="select2" required>

                                @if ($allClass->isEmpty())
                                    <option value="">Please Create Class First!!</option>
                                @else
                                    @foreach ($allClass as $class)
                                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-12 col-lg-6">
                        <div class="form-group">
                            <label class="mb-2 control-label" for="group_id_select">{{ _lang('Group') }}</label>
                            <select name="group_id" id="group_id_select"
                                class="form-select form-select-sm form-select-solid" data-control="select2">

                                @if ($allGroup->isEmpty())
                                    <option value="">Please Create Group First!!</option>
                                @else
                                    <option value="">{{ _lang('No Group (optional)') }}</option>
                                    @foreach ($allGroup as $group)
                                        <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="mt-2 mb-2 col-sm-12">
                        <label class="mb-2 control-label" for="subject_choice">
                            {{ _lang('Subject Choice') }}
                            <sup class="text-danger">*</sup>
                        </label>
                    </div>

                    @if ($allSubject->isEmpty())
                        <div class="col-sm-12">
                            <div class="form-check">
                                <label class="form-check-label" for="flexCheckDefault">
                                    Please Create Subject First!!
                                </label>
                            </div>
                        </div>
                    @else
                        @foreach ($allSubject as $subject)
                            <div class="col-lg-4">
                                <div class="form-check">
                                    <input class="my-2 form-check-input subject" name="subject[]" type="checkbox"
                                        value="{{ $subject->id }}" id="checkData">
                                    <label class="my-2 form-check-label" for="flexCheckDefault">
                                        {{ $subject->subject_name }} - {{ $subject->subject_code }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    @endif

                    <div class="float-left mt-5 col-sm-12">
                        <div class="form-group">
                            <button type="button" class="add_subject btn btn-primary">Save</button>
                        </div>
                    </div>
                </div>

            </form>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <th>{{ _lang('Subject') }}</th>
                        <th>{{ _lang('Subject Type') }}</th>
                        <th>{{ _lang('Subject Serial') }}</th>
                        <th>{{ _lang('Marge ID') }}</th>
                        <th>{{ _lang('Action') }}</th>
                    </thead>
                    <form method="POST">
                        <tbody id="tbody">

                        </tbody>

                        <tfoot>
                            <tr>
                                <td colspan="5">
                                    <div class="form-group float-end">
                                        <button type="button" class="dataEdit_button btn btn-primary">UPDATE</button>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>

                    </form>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {
            //Filter
            function getFilterData(class_id, group_id) {
                var class_id = class_id;
                var group_id = group_id;
                var data = {
                    'class_id': class_id,
                    'group_id': group_id,
                }
                // console.log('Data:', data);
                // Group is optional at the database level (nullable group_id,
                // handled correctly on the backend) — a class with no student
                // groups is a normal, valid configuration. Gating this on
                // group_id being non-empty meant the results table never
                // refreshed for any groupless class: the subject config was
                // actually saved successfully, but the screen looked exactly
                // like nothing had happened. Only Class is required here now,
                // matching the backend's actual validation rules.
                if (data.class_id !== '') {
                    $.ajax({
                        type: "GET",
                        url: "/subject-config",
                        data: data,
                        success: function(response) {
                            var stData = response.message;
                            var html = '';
                            if (Array.isArray(stData) && stData.length > 0) {
                                for (let i = 0; i < stData.length; i++) {
                                    html += `<tr>
                                    <td class="d-none"><input type="text" name="subject_id[]" value="${stData[i]['id']}" class="subject_id form-control"></td>
                                    <td><p class="mt-1">${stData[i]['subject_name']} - ${stData[i]['subject_code']}</p></td>
                                    <td>
                                        <div class="form-group">
                                            <select name="subject_type[]" id="subject_type" class="subject_type form-control select2" required>
                                                <option value="compulsary" ${stData[i]['subject_type'] === 'compulsary' ? 'selected' : ''}>COMPULSARY</option>
                                                <option value="choosable" ${stData[i]['subject_type'] === 'choosable' ? 'selected' : ''}>CHOOSABLE</option>
                                                <option value="group based" ${stData[i]['subject_type'] === 'group based' ? 'selected' : ''}>GROUP BASED</option>
                                                <option value="uncountable" ${stData[i]['subject_type'] === 'uncountable' ? 'selected' : ''}>UNCOUNTABLE</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td><input type="text" class="subject_serial form-control" id="subject_serial" name="subject_serial[]" value="${stData[i]['sr_no']}" required></td>
                                    <td><input type="text" class="merge_id form-control" id="merge_id" name="merge_id[]" value="${stData[i]['merge_config_id']}" required></td>
                                    <td><button type="button" value="${stData[i]['id']}" class="delete_button btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></td>
                                </tr>`
                                }
                            } else {
                                html += `<tr>
                                            <td colspan="5">No Data Found</td>
                                        </tr>`
                            }
                            $('#tbody').html(html);
                        },
                        error: function(xhr) {
                            // Previously there was no error handler at all, so
                            // any failed request (expired session, server
                            // error, etc.) just did nothing — indistinguishable
                            // from "there's no data yet".
                            $('#tbody').html('<tr><td colspan="5" class="text-danger">' +
                                'Could not load subject config. Please refresh the page and try again.' +
                                '</td></tr>');
                            console.error('subject-config filter failed:', xhr.status, xhr.responseText);
                        }
                    });
                }

            }

            //Filter Subject Config
            $('#class_id_select,#group_id_select').on('change', function() {
                var class_id = $('#class_id_select').val();
                var group_id = $('#group_id_select').val();
                sessionStorage.setItem("classID", class_id);
                sessionStorage.setItem("groupID", group_id);
                getFilterData(class_id, group_id);
            });

            // Restore the last-used Class/Group on page load. Without this,
            // saving a config, navigating away, and coming back showed an
            // empty screen even though the data was still there — easy to
            // mistake for "it didn't save".
            var storedClassID = sessionStorage.getItem("classID");
            var storedGroupID = sessionStorage.getItem("groupID");
            if (storedClassID) {
                $('#class_id_select').val(storedClassID).trigger('change.select2');
                if (storedGroupID) {
                    $('#group_id_select').val(storedGroupID).trigger('change.select2');
                }
                getFilterData(storedClassID, storedGroupID || '');
            }

            //Store Subject Config
            $(document).on('click', '.add_subject', function(e) {
                e.preventDefault();

                var subjects = $('input[name="subject[]"]:checked').map(function() {
                    return $(this).val();
                }).get();

                // console.log('data:', subjects);

                var class_id = $('#class_id_select').val();
                var group_id = $('#group_id_select').val();

                var data = {
                    'class_id': class_id,
                    'group_id': group_id,
                    'subjects': subjects,
                }
                // console.log('data', data);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "/subject-config",
                    data: data,
                    dataType: "json",
                    success: function(response) {
                        if (response.status == 400) {
                            toastr.warning(response.success);
                        } else {
                            getFilterData(class_id, group_id);
                            if (response.status == 200) {

                                toastr.success(response.success);
                            } else {
                                toastr.warning(response.success);
                            }
                        }
                    },
                    error: function(xhr) {
                        // No error handler previously — a failed save (validation
                        // error, expired session, server error) looked identical
                        // to a successful one from the user's point of view:
                        // nothing happened on screen either way.
                        var message = 'Could not save the subject config. Please try again.';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.status === 419) {
                            message = 'Your session has expired. Please refresh the page and try again.';
                        }
                        toastr.error(message);
                        console.error('subject-config save failed:', xhr.status, xhr.responseText);
                    }
                });
            });

            // Delete Data

            $(document).on('click', '.delete_button', function(e) {
                e.preventDefault();
                var subject_id = $(this).val();
                // console.log('subject_id: ', subject_id);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "DELETE",
                    url: "/subject-config/" + subject_id,
                    success: function(response) {
                        // console.log(response);
                        if (response.status == 200) {
                            toastr.success(response.success);
                            let classID = sessionStorage.getItem("classID");
                            let groupID = sessionStorage.getItem("groupID");
                            getFilterData(classID, groupID);
                        } else {
                            toastr.warning(response.success);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Could not delete this subject config. Please try again.');
                        console.error('subject-config delete failed:', xhr.status, xhr.responseText);
                    }
                });
            });

            // Update Data
            $(document).on('click', '.dataEdit_button', function(e) {
                e.preventDefault();

                var merge_id = new Array();
                $('.merge_id').each(function() {
                    merge_id.push($(this).val());
                });

                var subject_serial = new Array();
                $('.subject_serial').each(function() {
                    subject_serial.push($(this).val());
                });

                var subject_type = new Array();
                $('.subject_type').each(function() {
                    subject_type.push($(this).val());
                });

                var subject_id = new Array();
                $('.subject_id').each(function() {
                    subject_id.push($(this).val());
                });

                var data = {
                    'id': subject_id,
                    'subject_type': subject_type,
                    'subject_serial': subject_serial,
                    'merge_id': merge_id,
                }

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "PUT",
                    url: "/subject-config-update",
                    data: data,
                    dataType: "json",
                    success: function(response) {
                        if (response.status == 400) {
                            toastr.warning(response.success);
                        } else {
                            toastr.success(response.success);
                            let classID = sessionStorage.getItem("classID");
                            let groupID = sessionStorage.getItem("groupID");
                            getFilterData(classID, groupID);
                        }
                    },
                    error: function(xhr) {
                        var message = 'Could not update the subject config. Please try again.';
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        }
                        toastr.error(message);
                        console.error('subject-config-update failed:', xhr.status, xhr.responseText);
                    }
                });
            });

        });
    </script>
@endsection
