@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Member') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        <i class="entypo-plus-circled"></i>{{ _lang('Add New Member') }}
                    </div>
                </div>
                <div class="">
                    <div class="col-lg-12">
                        <form action="{{ route('librarymembers.store') }}" class="form-horizontal form-groups-bordered"
                            id="myform" enctype="multipart/form-data" method="post">
                            @csrf
                            <div class="form-group member_type">
                                <div class="col-sm-12">
                                    <label class="control-label my-2">{{ _lang('Member Type') }}</label>
                                    <select name="member_type" class="form-select form-select-sm form-select-solid"
                                        data-control="select2" required>

                                        <option value="Student">{{ _lang('Student') }}</option>
                                        <option value="Teacher">{{ _lang('Teacher') }}</option>
                                        <option value="Staff">{{ _lang('Staff') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="student" style="display: none;">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Class') }}</label>
                                        <select name="class_id" class="form-control select2 stu">

                                            {!! create_option('classes', 'id', 'class_name', old('class_id')) !!}
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Section') }}</label>
                                        <select name="section_id" class="form-control select2 stu">

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Student') }}</label>
                                        <select name="student_id" class="form-control select2 stu">

                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="teacher" style="display: none;">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Teacher') }}</label>
                                        <select name="teacher_id" class="form-control select2 tea">

                                            @foreach ($teachers as $data)
                                                <option value="{{ $data->user_id }}">{{ $data->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="staff" style="display: none;">
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <label class="control-label my-2">{{ _lang('Staff') }}</label>
                                        <select name="staff_id" class="form-control select2 sta">

                                            @foreach ($staffs as $data)
                                                <option value="{{ $data->user_id }}">{{ $data->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="p"></div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Add Member') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection
@section('js-script')
    <script type="text/javascript">
        $('document').ready(function() {
            $('.student').hide();
            $('.teacher').hide();
            $('.staff').hide();
            $('.member_type').on('change', function() {
                var memberType = $('select[name=member_type]').val();
                $('.btn-danger').show();
                if (memberType == 'Student') {
                    $('.member_type').hide();
                    $('.student').show();
                    $(".stu").prop('required', true);
                    $('select[name=student_id]').attr('name', 'user_id');
                } else if (memberType == 'Teacher') {
                    $('.member_type').hide();
                    $('.teacher').show();
                    $(".tea").prop('required', true);
                    $('select[name=teacher_id]').attr('name', 'user_id')
                } else {
                    $('.member_type').hide();
                    $('.staff').show();
                    $(".sta").prop('required', true);
                    $('select[name=staff_id]').attr('name', 'user_id')
                }
            });
            $('select[name=class_id]').on('change', function() {
                var _token = $('input[name=_token]').val();
                var class_id = $('select[name=class_id]').val();
                $.ajax({
                    type: "POST",
                    url: "{{ url('librarymembers-section') }}",
                    data: {
                        _token: _token,
                        class_id: class_id
                    },
                    success: function(data) {
                        $('select[name=section_id]').html(data);
                    }
                });
            });
            $('select[name=section_id]').on('change', function() {
                var _token = $('input[name=_token]').val();
                var section_id = $('select[name=section_id]').val();
                $.ajax({
                    type: "POST",
                    url: "{{ url('librarymembers-student') }}",
                    data: {
                        _token: _token,
                        section_id: section_id
                    },
                    success: function(data) {
                        $('select[name=user_id]').html(data);
                    }
                });
            });
        });
    </script>
@stop
