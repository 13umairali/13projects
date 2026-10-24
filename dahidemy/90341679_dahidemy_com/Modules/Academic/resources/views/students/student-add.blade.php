@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Student') }}
@endsection
@section('create-button')
    <a class="btn btn-sm fw-bold btn-primary" href="{{ route('students.bulk-file-upload') }}">
        {{ _lang('CSV/Xlsx Upload File') }}
    </a>
@endsection
@section('content')
    <form action="{{ route('students.store') }}" enctype="multipart/form-data" method="post" data-disable-on-submit>
        @csrf

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('First Name') }} <span class='text-danger'>*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name"
                        value="{{ old('first_name') }}" placeholder="First Name" required>

                    @error('first_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Last Name') }} <span
                            class="text-info">(optional)</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name"
                        value="{{ old('last_name') }}" placeholder="Last Name">

                    @error('last_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Father Name') }} <span
                            class="text-info">(optional)</span></label>
                    <input type="text" class="form-control" name="father_name" value="{{ old('father_name') }}"
                        placeholder="Father Name">

                    @error('father_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Mother Name') }} <span
                            class="text-info">(optional)</span></label>
                    <input type="text" class="form-control" name="mother_name" value="{{ old('mother_name') }}"
                        placeholder="Mother Name">

                    @error('mother_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Class') }} <span class='text-danger'>*</span></label>
                    <select name="class_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                        id="class_id" required>

                        {!! create_option('classes', 'id', 'class_name', old('class_id')) !!}
                    </select>

                    @error('class_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Group') }} <span class="text-info">(optional)</span></label>
                    <select name="group" id="group" class="form-select form-select-sm form-select-solid"
                        data-control="select2">

                        {!! create_option('student_groups', 'id', 'group_name', old('group')) !!}
                    </select>

                    @error('group')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Section') }} <span class='text-danger'>*</span></label>
                    <select name="section_id" class="form-select form-select-sm form-select-solid" data-control="select2"
                        id="section_id">

                        @foreach ($sections as $data)
                            <option data-class="{{ $data->class_id }}" value="{{ $data->id }}"
                                {{ old('section_id') == $data->id ? 'selected' : '' }}>
                                {{ $data->section_name }}
                            </option>
                        @endforeach
                    </select>

                    @error('section_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Gender') }} <span
                            class="text-info">(optional)</span></label>
                    <select name="gender" id="gender" class="form-select form-select-sm form-select-solid"
                        data-control="select2">

                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>
                            {{ _lang('Male') }}</option>
                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>
                            {{ _lang('Female') }}</option>
                    </select>

                    @error('gender')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Roll No.') }} <span class='text-danger'>*</span></label>
                    <input type="number" class="form-control" name="roll" value="{{ old('roll') }}"
                        placeholder="Roll No." required>

                    @error('roll')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Registration No.') }}
                        <span class="text-info">(optional)</span>
                    </label>
                    <input type="number" class="form-control" name="registration_no"
                        value="{{ old('registration_no') }}" placeholder="Register NO">

                    @error('registration_no')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Blood Group') }} <span
                            class="text-info">(optional)</span></label>
                    <select name="blood_group" id="blood_group" class="form-select form-select-sm form-select-solid"
                        data-control="select2">

                        <option {{ old('blood_group') == 'A+' ? 'selected' : '' }} value="A+">N/A
                        </option>
                        <option {{ old('blood_group') == 'A+' ? 'selected' : '' }} value="A+">A+
                        </option>
                        <option {{ old('blood_group') == 'A-' ? 'selected' : '' }} value="A-">A-
                        </option>
                        <option {{ old('blood_group') == 'B+' ? 'selected' : '' }} value="B+">B+
                        </option>
                        <option {{ old('blood_group') == 'B-' ? 'selected' : '' }} value="B-">B-
                        </option>
                        <option {{ old('blood_group') == 'AB+' ? 'selected' : '' }} value="AB+">AB+
                        </option>
                        <option {{ old('blood_group') == 'AB-' ? 'selected' : '' }} value="AB-">AB-
                        </option>
                        <option {{ old('blood_group') == 'O+' ? 'selected' : '' }} value="O+">O+
                        </option>
                        <option {{ old('blood_group') == 'O-' ? 'selected' : '' }} value="O-">O-
                        </option>
                    </select>

                    @error('blood_group')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Religion') }} <span
                            class="text-info">(optional)</span></label>
                    <select name="religion" id="religion" class="form-select form-select-sm form-select-solid"
                        data-control="select2">

                        {!! create_option('picklists', 'value', 'value', old('religion'), ['type' => 'Religion']) !!}
                    </select>

                    @error('religion')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="form-group">
                <label class="my-2 control-label">{{ _lang('Admission Number') }}
                    <span class="text-info">(optional)</span>
                </label>
                <input type="number" class="form-control" name="qr_code" value="{{ old('qr_code') }}"
                    placeholder="Admission Number">

                @error('qr_code')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-12">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Address') }} <span
                            class="text-info">(optional)</span></label>
                    <textarea class="form-control" name="address" id='address' cols="2" rows="3">{{ old('address') }}</textarea>

                    @error('address')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="my-5 text-center col-lg-6">
                <h2 class="block my-5 text-primary font_size_medium">
                    <i class="bi bi-people-fill font_size_medium"></i>
                    <strong>Guardian Information</strong>
                </h2>
                <p class="text-muted">
                    Please provide the guardian's details for communication and emergency contact purposes.
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">
                        {{ _lang('Guardian Name') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" name="information_sent_to_name"
                        value="{{ old('information_sent_to_name') }}" placeholder="Enter Guardian Full Name" required>

                    @error('information_sent_to_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">
                        {{ _lang('Guardian Email Address') }}
                        <span class="text-info">(Optional)</span>
                    </label>
                    <input type="email" class="form-control" name="information_sent_to_email"
                        value="{{ old('information_sent_to_email') }}" placeholder="Enter Guardian Email Address">

                    @error('information_sent_to_email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">
                        {{ _lang('Guardian Phone Number') }}
                        <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" name="information_sent_to_phone"
                        value="{{ old('information_sent_to_phone') }}" placeholder="Enter Guardian Phone Number"
                        required>

                    @error('information_sent_to_phone')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">
                        {{ _lang('Relationship') }}
                        <span class="text-danger">*</span>
                    </label>
                    <select class="form-control" name="information_sent_to_relation" data-control="select2" required>
                        <option value="">Select Relationship</option>
                        <option value="Father" {{ old('information_sent_to_relation') == 'Father' ? 'selected' : '' }}>
                            Father</option>
                        <option value="Mother" {{ old('information_sent_to_relation') == 'Mother' ? 'selected' : '' }}>
                            Mother</option>
                        <option value="Brother" {{ old('information_sent_to_relation') == 'Brother' ? 'selected' : '' }}>
                            Brother</option>
                        <option value="Sister" {{ old('information_sent_to_relation') == 'Sister' ? 'selected' : '' }}>
                            Sister</option>
                        <option value="Uncle" {{ old('information_sent_to_relation') == 'Uncle' ? 'selected' : '' }}>
                            Uncle</option>
                        <option value="Aunt" {{ old('information_sent_to_relation') == 'Aunt' ? 'selected' : '' }}>Aunt
                        </option>
                        <option value="Grandfather"
                            {{ old('information_sent_to_relation') == 'Grandfather' ? 'selected' : '' }}>Grandfather
                        </option>
                        <option value="Grandmother"
                            {{ old('information_sent_to_relation') == 'Grandmother' ? 'selected' : '' }}>Grandmother
                        </option>
                        <option value="Other" {{ old('information_sent_to_relation') == 'Other' ? 'selected' : '' }}>
                            Other</option>
                    </select>

                    @error('information_sent_to_relation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <label class="my-2 control-label">
                        {{ _lang('Guardian Address') }}
                        <span class="text-info">(Optional)</span>
                    </label>
                    <textarea class="form-control" name="information_sent_to_address" rows="3"
                        placeholder="Enter Guardian Address">{{ old('information_sent_to_address') }}</textarea>

                    @error('information_sent_to_address')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Student Login Credentials -->
        <div class="row justify-content-center">
            <div class="my-5 text-center col-lg-6">
                <h2 class="block my-5 text-primary font_size_medium">
                    <i class="bi bi-lock-fill font_size_medium"></i>
                    <strong>Login Details</strong>
                </h2>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">
                        {{ _lang('Email') }}
                        <span class='text-danger'>*</span>
                    </label>
                    <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                        placeholder="Email" required>

                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Phone Number') }} <span
                            class='text-danger'>*</span></label>
                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                        id="user_phone" min="10" max="15" placeholder="Phone" required>

                    @error('phone')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-6">
                @php
                    $random6DigitPassword = 123456;
                @endphp
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Password') }}</label>
                    <input type="text" class="form-control" name="password"
                        value="{{ old('password', $random6DigitPassword) }}" placeholder="Enter Password">

                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="my-2 control-label">{{ _lang('Confirm Password') }}</label>
                    <input type="text" class="form-control" name="password_confirmation"
                        value="{{ old('password_confirmation', $random6DigitPassword) }}"
                        placeholder="Password Confirmation">

                    @error('password_confirmation')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <!--begin::Thumbnail settings-->
            <div class="py-4 card card-flush">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title">
                        <h2>Student Picture <span class="text-info">(optional)</span></h2>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="pt-0 text-center card-body">
                    <!--begin::Image input-->
                    <!--begin::Image input placeholder-->
                    <style>
                        .image-input-placeholder {
                            background-image: url('{{ asset('assets/media/svg/files/blank-image.svg') }}');
                        }

                        [data-theme="dark"] .image-input-placeholder {
                            background-image: url('{{ asset('assets/media/svg/files/blank-image-dark.svg') }}');
                        }
                    </style>

                    <!--end::Image input placeholder-->
                    <div class="mb-3 image-input image-input-empty image-input-outline image-input-placeholder"
                        data-kt-image-input="true">
                        <!--begin::Preview existing image-->
                        <div class="image-input-wrapper w-150px h-150px"></div>
                        <!--end::Preview existing image-->
                        <!--begin::Label-->
                        <label class="shadow btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body"
                            data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change image">
                            <i class="bi bi-pencil-fill fs-7"></i>
                            <!--begin::Inputs-->
                            <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                            <input type="hidden" name="image_remove" />
                            <!--end::Inputs-->
                        </label>
                        <!--end::Label-->
                        <!--begin::Cancel-->
                        <span class="shadow btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body"
                            data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel image">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                        <!--end::Cancel-->
                        <!--begin::Remove-->
                        <span class="shadow btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body"
                            data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove image">
                            <i class="bi bi-x fs-2"></i>
                        </span>
                        <!--end::Remove-->
                    </div>
                    <!--end::Image input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">Set the product thumbnail image. Only *.png, *.jpg and *.jpeg image files
                        are accepted</div>
                    <!--end::Description-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Thumbnail settings-->
        </div>

        <div class="my-5 form-group">
            <button type="submit" class="mt-5 btn btn-primary">{{ _lang('Create Student') }}</button>
        </div>
    </form>
@endsection

@section('js-script')
    <script>
        $(window).on('load', function() {
            // The Section dropdown used to be pre-loaded with every section
            // from every class at once, then "filtered" by directly poking
            // at Select2's internally-rendered dropdown list items
            // ($("#section_id").next().find("ul li")). That list only
            // exists in the DOM while the dropdown happens to be open (it's
            // appended to <body>, not next to the select), so in the normal
            // case of picking a Class and then opening Section, the filter
            // never actually ran — every section from every class stayed
            // selectable. Picking a section that didn't belong to the
            // chosen class then failed server-side validation
            // ("The selected section does not belong to the chosen class"),
            // which is what "Add Student form fails to save" looked like.
            //
            // Fixed to actually fetch the right sections from the server
            // (an endpoint that already existed and was already correct —
            // it just wasn't wired up here) and rebuild the dropdown.
            function reloadSections(classId) {
                var $section = $('#section_id');

                if (!classId) {
                    if ($section.data('select2')) {
                        $section.select2('destroy');
                    }
                    $section.html('<option value="">' + '{{ _lang('Select Class First') }}' + '</option>');
                    $section.select2({
                        dir: document.body.getAttribute('direction')
                    });
                    $section.attr('data-kt-initialized', '1');
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: '{{ url('sections-section') }}',
                    data: {
                        class_id: classId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if ($section.data('select2')) {
                            $section.select2('destroy');
                        }
                        $section.html(response);
                        $section.select2({
                            dir: document.body.getAttribute('direction')
                        });
                        $section.attr('data-kt-initialized', '1');
                    },
                    error: function(xhr) {
                        toastr.error('{{ _lang('Could not load sections for this class. Please try again.') }}');
                        console.error('sections-section failed:', xhr.status, xhr.responseText);
                    }
                });
            }

            $(document).on('change', '#class_id', function() {
                reloadSections($(this).val());
            });

            // If a class was already picked (e.g. after a validation error
            // sent the form back with old() input), load its real sections
            // immediately instead of leaving the stale full list in place.
            var initialClassId = $('#class_id').val();
            if (initialClassId) {
                reloadSections(initialClassId);
            }
        });
    </script>
@stop
