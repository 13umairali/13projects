@extends('layouts.backend')


@section('content')
    <div class="card">


        <div class="card-header">

            <h4>
                Edit WhatsApp Template
            </h4>


        </div>



        <div class="card-body">

            <form method="POST" action="{{ route('whats-app-templates.update', $template->id) }}">
                @csrf
                @method('PUT')

                <div class="row">


                    <div class="col-md-6 mb-3">


                        <label>
                            Template Name
                        </label>


                        <input name="name" class="form-control" value="{{ old('name', $template->name) }}" placeholder="Fee Reminder">


                    </div>





                    <div class="col-md-6 mb-3">


                        <label>
                            Event
                        </label>


                        <select name="event" class="form-control">


                            <option value="fee_due" @selected(old('event', $template->event) == 'fee_due')>
                                Fee Due
                            </option>


                            <option value="fee_collected" @selected(old('event', $template->event) == 'fee_collected')>
                                Fee Collected
                            </option>


                            <option value="absent" @selected(old('event', $template->event) == 'absent')>
                                Absent
                            </option>


                            <option value="result" @selected(old('event', $template->event) == 'result')>
                                Result
                            </option>


                            <option value="notice" @selected(old('event', $template->event) == 'notice')>
                                Notice
                            </option>



                            <option value="admission_submitted" @selected(old('event', $template->event) == 'admission_submitted')>
                                Admission Submitted
                            </option>


                            <option value="admission_approved" @selected(old('event', $template->event) == 'admission_approved')>
                                Admission Approved
                            </option>


                            <option value="admission_rejected" @selected(old('event', $template->event) == 'admission_rejected')>
                                Admission Rejected
                            </option>


                            <option value="form_submitted" @selected(old('event', $template->event) == 'form_submitted')>
                                Form Submitted
                            </option>


                            <option value="form_approved" @selected(old('event', $template->event) == 'form_approved')>
                                Form Approved
                            </option>


                            <option value="form_rejected" @selected(old('event', $template->event) == 'form_rejected')>
                                Form Rejected
                            </option>


                            <option value="event_enrollment_confirmed" @selected(old('event', $template->event) == 'event_enrollment_confirmed')>
                                Event Enrollment Confirmed (Free)
                            </option>


                            <option value="event_enrollment_paid" @selected(old('event', $template->event) == 'event_enrollment_paid')>
                                Event Enrollment Confirmed (Paid)
                            </option>


                            <option value="result_published" @selected(old('event', $template->event) == 'result_published')>
                                Result Published
                            </option>


                            <option value="course_enrollment_confirmed" @selected(old('event', $template->event) == 'course_enrollment_confirmed')>
                                Course Enrollment Confirmed (Free)
                            </option>


                            <option value="course_enrollment_paid" @selected(old('event', $template->event) == 'course_enrollment_paid')>
                                Course Enrollment Confirmed (Paid)
                            </option>

                        </select>


                    </div>


                    <div class="col-md-12 mb-3">


                        <label>
                            Message
                        </label>


                        <textarea name="message" rows="6" class="form-control"
                            placeholder="Hello @{{student_name}} Your fee is pending">{{ old('message', $template->message) }}</textarea>
                        <small class="form-text text-muted">Available placeholders depend on the event: student_name, student_id, class, section, roll, date, status, invoice_number, collection_date, amount_paid, due_amount, payment_method, receipt_number, school_name, contact. For the new admission_* and form_* events: student_name, school_name, applicant_name, form_name, login_url, password, remarks.</small>
                    </div>


                    <div class="col-md-3 mb-3">


                        <label>
                            Status
                        </label>


                        <select name="status" class="form-control">


                            <option value="1" @selected(old('status', $template->status) == 1)>
                                Active
                            </option>


                            <option value="0" @selected(old('status', $template->status) == 0)>
                                Inactive
                            </option>


                        </select>


                    </div>



                </div>





                <button class="btn btn-primary">

                    Save

                </button>


            </form>



        </div>


    </div>
@endsection
