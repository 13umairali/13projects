  <div id="sms" class="tab-pane fade">
      <div class="card card-default border p-5 my-2">
          <div class="card-header">
              <span class="card-title">{{ _lang('SMS Settings') }}</span>
          </div>
          <div class="">
              <form method="post" action="{{ url('administration/general_settings/update') }}"
                  enctype="multipart/form-data">
                  @csrf

                  <div class="row">
                      <div class="col-lg-6">
                          <div class="form-group">
                              <label class="control-label my-2">{{ _lang('SMS Type') }}</label>
                              <select class="form-select" name="sms_type">
                                  <option value="nonmasking">{{ _lang('General SMS') }}</option>
                                  <option value="masking">{{ _lang('Masking SMS') }}</option>
                              </select>
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="sender_id" class="control-label my-2">Sender Id</label>
                              <input type="text" id="sender_id" name="sender_id" class="form-control"
                                  value="{{ get_option('sender_id') }}">
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="api_key" class="control-label my-2">Api Key</label>
                              <input type="text" id="api_key" name="api_key" class="form-control"
                                  value="{{ get_option('api_key') }}">
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="sms_api" class="control-label my-2">SMS Api</label>
                              <input type="text" id="sms_api" name="sms_api" class="form-control"
                                  value="{{ get_option('sms_api') }}">
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="default_sms_gateway" class="control-label my-2">SMS
                                  Gateway</label>
                              <select id="default_sms_gateway" name="default_sms_gateway" class="form-select">
                                  <option value="sms4bd"
                                      {{ get_option('default_sms_gateway') == 'sms4bd' ? 'selected' : '' }}>
                                      SMS4BD</option>
                                  <option value="bulkSMSBD"
                                      {{ get_option('default_sms_gateway') == 'bulkSMSBD' ? 'selected' : '' }}>
                                      Bulk SMS BD</option>
                              </select>
                          </div>
                      </div>

                      <div class="col-lg-6">
                          <div class="form-group">
                              <label for="sms_test_mode" class="control-label my-2">SMS Test Mode</label>
                              <select id="sms_test_mode" name="sms_test_mode" class="form-select">
                                  <option value="true" {{ get_option('sms_test_mode') == 'true' ? 'selected' : '' }}>
                                      True
                                  </option>
                                  <option value="false"
                                      {{ get_option('sms_test_mode') == 'false' ? 'selected' : '' }}>False
                                  </option>
                              </select>
                          </div>
                      </div>

                      <div class="form-group">
                          <div class="col-lg-12">
                              <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save Settings') }}</button>
                          </div>
                      </div>
                  </div>
              </form>
          </div>
      </div>
  </div>
