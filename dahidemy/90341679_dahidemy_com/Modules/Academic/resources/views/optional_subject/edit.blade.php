@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Edit Optional Subject') }}
@endsection




@section('content')
    <div class="row">


        <div class="col-lg-12">



            <div class="card">


                <div class="card-header">


                    <div class="card-title">

                        {{ _lang('Update Optional Subject') }}

                    </div>


                </div>



                <div class="card-body">





                    <form method="POST" action="{{ route('optional-subject-config.update', $config->id) }}">


                        @csrf

                        @method('PUT')





                        <div class="row">


                            <div class="col-lg-6">


                                <label>

                                    Class

                                </label>


                                <select name="class_id" id="class_id" class="form-control" data-control="select2">


                                    {!! create_option('classes', 'id', 'class_name', $config->class_id) !!}


                                </select>


                            </div>





                            <div class="col-lg-6">


                                <label>

                                    Group

                                </label>


                                <select name="group_id" id="group_id" class="form-control" data-control="select2">


                                    {!! create_option('student_groups', 'id', 'group_name', $config->group_id) !!}



                                </select>



                            </div>



                        </div>






                        <div class="row mt-3">


                            <div class="col-lg-6">


                                <label>

                                    Name

                                </label>


                                <input type="text" name="name" class="form-control" value="{{ $config->name }}">


                            </div>





                            <div class="col-lg-3">


                                <label>

                                    Total Mark

                                </label>


                                <input type="number" name="total_mark" class="form-control"
                                    value="{{ $config->total_mark }}">


                            </div>





                            <div class="col-lg-3">


                                <label>

                                    Limit

                                </label>


                                <input type="number" name="selection_limit" class="form-control"
                                    value="{{ $config->selection_limit }}">


                            </div>



                        </div>







                        <div class="mt-5">


                            <label>

                                Subjects

                            </label>



                            <select name="subjects[]" id="subjects" multiple class="form-control" data-control="select2">


                                @foreach ($config->items as $item)
                                    <option selected value="{{ $item->subject_id }}">


                                        {{ $item->subject->subject_name }}


                                    </option>
                                @endforeach



                            </select>


                        </div>






                        <button class="btn btn-primary mt-5">


                            Update


                        </button>



                    </form>




                </div>


            </div>


        </div>


    </div>
@endsection





@section('js-script')
    <script>
        $('#class_id,#group_id').change(function() {


            let class_id =
                $('#class_id').val();


            let group_id =
                $('#group_id').val();




            $.ajax({


                url: "{{ route('optional-subject.getSubjects') }}",


                data: {


                    class_id: class_id,

                    group_id: group_id


                },



                success: function(data) {

                    // Destroy/rebuild Select2 so the new option list actually
                    // renders (see the same fix + explanation in create.blade.php).
                    // Also: this used to try to re-select the *original*
                    // config's subject IDs after every class/group change,
                    // even though those subjects belong to the class you just
                    // moved away from and won't be in the new list — that was
                    // dead code, not a real "restore my selection" feature,
                    // so it's removed. The original subjects are still
                    // correctly pre-selected on first page load via the
                    // server-rendered <option selected> below.
                    if ($('#subjects').data('select2')) {
                        $('#subjects').select2('destroy');
                    }
                    $('#subjects').html(data);
                    $('#subjects').select2({
                        dir: document.body.getAttribute('direction')
                    });
                    $('#subjects').attr('data-kt-initialized', '1');

                }



            });


        });
    </script>
@endsection
