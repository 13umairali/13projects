<div id="websiteDesign" class="tab-pane fade">
    <div class="p-5 my-2 border card card-default">
        <div class="card-header">
            <span class="card-title">{{ _lang('Website Design') }}</span>
            <span class="d-block text-muted small mt-1">
                {{ _lang('Choose how your public website looks. Changes apply the next time a visitor loads your site.') }}
            </span>
        </div>

        <div class="">
            <form method="post" action="{{ url('administration/general_settings/update') }}">
                @csrf

                {{-- Theme picker --}}
                <div class="row mb-4">
                    @php
                        $currentTheme = get_option('website_theme', 'theme-one');
                        $themeOptions = [
                            'theme-one' => [
                                'label' => 'Aurora',
                                'description' => 'Bold, modern, gradient-driven — animated hero and glass-style cards.',
                                'swatches' => ['#7c3aed', '#0f172a', '#ec4899'],
                            ],
                            'theme-two' => [
                                'label' => 'Northline',
                                'description' => 'Clean, editorial, corporate — sharp type and generous whitespace.',
                                'swatches' => ['#0f766e', '#0b1220', '#f59e0b'],
                            ],
                        ];
                    @endphp

                    <div class="col-lg-12">
                        <label class="my-2 control-label d-block">{{ _lang('Theme') }}</label>
                        <div class="row">
                            @foreach ($themeOptions as $value => $option)
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <label class="w-100" style="cursor: pointer;">
                                        <div class="border rounded p-3 h-100 {{ $currentTheme === $value ? 'border-primary border-2' : '' }}">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <strong>{{ $option['label'] }}</strong>
                                                <input type="radio" name="website_theme" value="{{ $value }}"
                                                    {{ $currentTheme === $value ? 'checked' : '' }}>
                                            </div>
                                            <div class="mb-2">
                                                @foreach ($option['swatches'] as $swatch)
                                                    <span class="d-inline-block rounded-circle me-1"
                                                        style="width:16px;height:16px;background:{{ $swatch }};border:1px solid rgba(0,0,0,.1);"></span>
                                                @endforeach
                                            </div>
                                            <div class="small text-muted">{{ $option['description'] }}</div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Brand colors --}}
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Primary Color') }}</label>
                            <input type="color" class="form-control form-control-color" name="website_primary_color"
                                value="{{ get_option('website_primary_color', '#7c3aed') }}">
                            <small class="text-muted">{{ _lang('Buttons, links, highlighted text.') }}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Secondary Color') }}</label>
                            <input type="color" class="form-control form-control-color" name="website_secondary_color"
                                value="{{ get_option('website_secondary_color', '#0f172a') }}">
                            <small class="text-muted">{{ _lang('Headings, dark backgrounds, footer.') }}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="my-2 control-label">{{ _lang('Accent Color') }}</label>
                            <input type="color" class="form-control form-control-color" name="website_accent_color"
                                value="{{ get_option('website_accent_color', '#ec4899') }}">
                            <small class="text-muted">{{ _lang('Gradients, hover states, small details.') }}</small>
                        </div>
                    </div>

                    <div class="col-lg-12 mt-4 d-flex align-items-center">
                        <button class="btn btn-primary me-3">
                            {{ _lang('Save Website Design') }}
                        </button>
                        <a href="{{ route('index') }}" target="_blank" class="btn btn-outline-secondary">
                            {{ _lang('Preview Website') }}
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
