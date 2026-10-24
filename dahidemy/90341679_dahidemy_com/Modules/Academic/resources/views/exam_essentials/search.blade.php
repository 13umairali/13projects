@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Admit Card And Seating Plan') }}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ _lang('Generate Seating Plan / Admit Card') }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('exam-essentials.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label" for="class_id_select">{{ _lang('Class') }}</label>
                                    <select name="class_id" id="class_id_select" class="form-control select2" required>
                                        {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label">{{ _lang('Section') }}</label>
                                    <select name="section_id" class="form-control select2">
                                        @foreach ($sections as $section)
                                            <option value="{{ $section->id }}"
                                                {{ $section->id == request()->section_id ? 'selected' : '' }}>
                                                {{ $section->section_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label"
                                        for="class_id_select">{{ _lang('From Roll No. (Optional)') }}</label>
                                    <input name="from_roll" type="number" class="form-control"
                                        value="{{ old('from_roll') }}">
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="my-2 control-label">{{ _lang('To') }}</label>
                                    <input name="to_roll" type="number" class="form-control"
                                        value="{{ old('to_roll') }}">
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <button type="submit" name="seat_plan" value="1"
                                        class="btn btn-primary btn-block rect-btn"> <i class="fa fa-th-large"
                                            aria-hidden="true"></i> {{ _lang('Seating Plan') }}</button>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <button type="submit" name="admit_card" value="1"
                                        class="btn btn-success btn-block rect-btn"> <i class="fa fa-id-card"
                                            aria-hidden="true"></i> {{ _lang('Admit Card') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    <script>
        $("#class_id_select").change(function() {
            var class_id = $(this).val();
            var _token = $('input[name=_token]').val();
            var class_id = $('select[name=class_id]').val();
            $.ajax({
                type: "POST",
                url: "{{ url('sections-section-group-wise') }}",
                data: {
                    _token: _token,
                    class_id: class_id
                },
                beforeSend: function() {
                    $("#preloader").css("display", "block");
                },
                success: function(sections) {
                    $("#preloader").css("display", "none");
                    $('select[name=section_id]').html(sections);
                }
            });
        });
    </script>
@stop
