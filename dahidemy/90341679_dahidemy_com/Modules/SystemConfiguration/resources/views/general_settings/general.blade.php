 <div id="general" class="tab-pane fade show active">
     <div class="p-5 my-2 border card card-default">
         <div class="card-header">
             <span class="card-title">{{ _lang('General Settings') }}</span>
         </div>

         <div class="">
             <form method="post" action="{{ url('administration/general_settings/update') }}"
                 enctype="multipart/form-data">
                 @csrf

                 <div class="row">
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Institute Name') }}</label>
                             <input type="text" class="form-control" name="school_name"
                                 value="{{ get_option('school_name') }}">
                         </div>
                     </div>

                     {{-- <div class="col-lg-6">
                         <div class="form-group">
                             <label for="theme_id" class="my-2 control-label">{{ _lang('Theme') }}</label>
                             <select id="theme_id" name="theme_id" class="form-control select2">
                                 <option value="yes" {{ get_option('theme_id') == 'yes' ? 'selected' : '' }}>Yes
                                 </option>
                                 <option value="no" {{ get_option('theme_id') == 'no' ? 'selected' : '' }}>No
                                 </option>
                             </select>
                         </div>
                     </div> --}}

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Site Title') }}</label>
                             <input type="text" class="form-control" name="site_title"
                                 value="{{ get_option('site_title') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Phone') }}</label>
                             <input type="text" class="form-control" name="phone"
                                 value="{{ get_option('phone') }}">
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Tuition Fee') }}</label>
                             <input type="text" class="form-control" name="tuition_fee_phone"
                                 value="{{ get_option('tuition_fee_phone') }}">
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Exam and result') }}</label>
                             <input type="text" class="form-control" name="exam_result_phone"
                                 value="{{ get_option('exam_result_phone') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Email') }}</label>
                             <input type="text" class="form-control" name="email"
                                 value="{{ get_option('email') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Address') }}</label>
                             <textarea class="form-control" name="address">{{ get_option('address') }}</textarea>
                         </div>
                     </div>
                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('EIIN') }}</label>
                             <textarea class="form-control" name="eiin_code">{{ get_option('eiin_code') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Institute ID') }}</label>
                             <input type="text" class="form-control" name="institute_code"
                                 value="{{ get_option('institute_code') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Transfer Certificate Fee') }}</label>
                             <input type="text" class="form-control" name="tc_amount"
                                 value="{{ get_option('tc_amount') }}">
                         </div>
                     </div>

                     <div class="col-lg-12">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Live Notice On Header') }}</label>
                             <textarea class="form-control" name="header_notice" cols="4" rows="4">{{ get_option('header_notice') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-12">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('CopyRight') }}</label>
                             <textarea class="form-control" name="copyright_text" cols="4" rows="4">{{ get_option('copyright_text') }}</textarea>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label for="admission_display_status"
                                 class="my-2 control-label">{{ _lang('Online Admission Display Status') }}</label>
                             <select id="admission_display_status" name="admission_display_status"
                                 class="form-control select2">
                                 <option value="yes"
                                     {{ get_option('admission_display_status') == 'yes' ? 'selected' : '' }}>Yes
                                 </option>
                                 <option value="no"
                                     {{ get_option('admission_display_status') == 'no' ? 'selected' : '' }}>No
                                 </option>
                             </select>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label for="exam_result_status"
                                 class="my-2 control-label">{{ _lang('Exam Results Display Status') }}</label>
                             <select id="exam_result_status" name="exam_result_status" class="form-control select2">
                                 <option value="yes"
                                     {{ get_option('exam_result_status') == 'yes' ? 'selected' : '' }}>Yes</option>
                                 <option value="no"
                                     {{ get_option('exam_result_status') == 'no' ? 'selected' : '' }}>No</option>
                             </select>
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('App Version') }}</label>
                             <input type="text" class="form-control" name="app_version"
                                 value="{{ get_option('app_version') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('App URL') }}</label>
                             <input type="text" class="form-control" name="app_url"
                                 value="{{ get_option('app_url') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('Play Store Link') }}</label>
                             <input type="text" class="form-control" name="play_store_link"
                                 value="{{ get_option('play_store_link') }}">
                         </div>
                     </div>

                     <div class="col-lg-6">
                         <div class="form-group">
                             <label class="my-2 control-label">{{ _lang('App Store Link') }}</label>
                             <input type="text" class="form-control" name="app_store_link"
                                 value="{{ get_option('app_store_link') }}">
                         </div>
                     </div>

                     <div class="form-group">
                         <div class="col-lg-12">
                             <button type="submit"
                                 class="mt-5 btn btn-primary">{{ _lang('Save Settings') }}</button>
                         </div>
                     </div>
                 </div>
             </form>
         </div>
     </div>
 </div>
