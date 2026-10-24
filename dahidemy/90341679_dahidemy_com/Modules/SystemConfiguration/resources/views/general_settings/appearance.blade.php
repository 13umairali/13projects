<div id="appearance" class="tab-pane fade">

    <div class="card border p-4 mb-4">
        <div class="card-header">
            <h5>{{ _lang('Theme Designer') }}</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-light-info d-flex align-items-center mb-5">
                <i class="fa fa-info-circle fs-3 me-3"></i>
                <div>
                    {{ _lang('These colors apply to both your public website and this admin dashboard. For each one, choose Solid (one color) or Gradient (two colors blending together), then either use the color picker or type an exact HEX/RGB code.') }}
                </div>
            </div>

            <form method="POST" action="{{ url('administration/general_settings/update') }}">
                @csrf

                @php
                    $colorSlots = [
                        ['key' => 'theme_primary', 'label' => 'Primary', 'default' => '#1b2a4a', 'default2' => '#101a2e', 'gradient' => true],
                        ['key' => 'theme_secondary', 'label' => 'Secondary / Accent', 'default' => '#b08d57', 'default2' => '#96723f', 'gradient' => true],
                        ['key' => 'theme_header', 'label' => 'Header', 'default' => '#fbfaf7', 'default2' => '#f3f1ea', 'gradient' => true],
                        ['key' => 'theme_footer', 'label' => 'Footer', 'default' => '#101a2e', 'default2' => '#1b2a4a', 'gradient' => true],
                        ['key' => 'theme_button', 'label' => 'Buttons', 'default' => '#b08d57', 'default2' => '#96723f', 'gradient' => true],
                        ['key' => 'theme_menu', 'label' => 'Menu (sidebar)', 'default' => '#ffffff', 'default2' => '#f5f8fa', 'gradient' => true],
                        ['key' => 'theme_submenu', 'label' => 'Active Menu Item', 'default' => '#1b2a4a', 'default2' => '#101a2e', 'gradient' => true],
                    ];
                @endphp

                <div class="row g-5 mb-4">
                    @foreach ($colorSlots as $slot)
                        @php
                            $currentType = get_option($slot['key'].'_type') ?: 'solid';
                            $color1 = get_option($slot['key'].'_color') ?: $slot['default'];
                            $color2 = get_option($slot['key'].'_color_2') ?: $slot['default2'];
                        @endphp
                        <div class="col-lg-6">
                            <div class="border rounded p-4 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="fw-bold mb-0">{{ _lang($slot['label']) }}</label>
                                    <select name="{{ $slot['key'] }}_type" class="form-select form-select-sm w-auto color-type-select" data-slot="{{ $slot['key'] }}">
                                        <option value="solid" @selected($currentType === 'solid')>{{ _lang('Solid') }}</option>
                                        <option value="gradient" @selected($currentType === 'gradient')>{{ _lang('Gradient') }}</option>
                                    </select>
                                </div>

                                <div class="row g-2 align-items-center">
                                    <div class="col-auto">
                                        <input type="color" class="form-control form-control-color"
                                            id="{{ $slot['key'] }}_color_picker" value="{{ $color1 }}"
                                            data-text-target="{{ $slot['key'] }}_color_text">
                                    </div>
                                    <div class="col">
                                        <input type="text" name="{{ $slot['key'] }}_color" id="{{ $slot['key'] }}_color_text"
                                            class="form-control" value="{{ $color1 }}" placeholder="#1b2a4a or rgb(27,42,74)"
                                            data-picker-target="{{ $slot['key'] }}_color_picker">
                                    </div>
                                </div>

                                <div class="row g-2 align-items-center mt-2 gradient-second-color" id="{{ $slot['key'] }}_gradient_row"
                                    style="{{ $currentType === 'gradient' ? '' : 'display:none;' }}">
                                    <div class="col-auto">
                                        <input type="color" class="form-control form-control-color"
                                            id="{{ $slot['key'] }}_color2_picker" value="{{ $color2 }}"
                                            data-text-target="{{ $slot['key'] }}_color2_text">
                                    </div>
                                    <div class="col">
                                        <input type="text" name="{{ $slot['key'] }}_color_2" id="{{ $slot['key'] }}_color2_text"
                                            class="form-control" value="{{ $color2 }}" placeholder="#101a2e or rgb(16,26,46)"
                                            data-picker-target="{{ $slot['key'] }}_color2_picker">
                                    </div>
                                </div>

                                <div class="mt-3 rounded" style="height: 36px; background: {{ $currentType === 'gradient' ? 'linear-gradient(135deg, '.$color1.', '.$color2.')' : $color1 }};" id="{{ $slot['key'] }}_preview"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <hr class="my-5">

                <div class="row g-4 mb-5">
                    <div class="col-lg-6">
                        <label class="fw-bold">{{ _lang('Body Text Color') }}</label>
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <input type="color" class="form-control form-control-color" id="theme_text_color_picker"
                                    value="{{ get_option('theme_text_color') ?: '#14181f' }}" data-text-target="theme_text_color_text">
                            </div>
                            <div class="col">
                                <input type="text" name="theme_text_color" id="theme_text_color_text" class="form-control"
                                    value="{{ get_option('theme_text_color') ?: '#14181f' }}" data-picker-target="theme_text_color_picker">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label class="fw-bold">{{ _lang('Hover / Link Color') }}</label>
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <input type="color" class="form-control form-control-color" id="theme_hover_color_picker"
                                    value="{{ get_option('theme_hover_color') ?: '#b08d57' }}" data-text-target="theme_hover_color_text">
                            </div>
                            <div class="col">
                                <input type="text" name="theme_hover_color" id="theme_hover_color_text" class="form-control"
                                    value="{{ get_option('theme_hover_color') ?: '#b08d57' }}" data-picker-target="theme_hover_color_picker">
                            </div>
                        </div>
                    </div>
                </div>

                <button class="btn btn-primary">
                    {{ _lang('Save Theme') }}
                </button>
            </form>
        </div>
    </div>

    {{-- Legacy fields kept working exactly as before for anything still reading them --}}
    <div class="card border p-4">
        <div class="card-header">
            <h5>{{ _lang('Legacy Appearance Fields') }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('administration/general_settings/update') }}">
                @csrf
                <div class="row">
                    <div class="col-lg-3">
                        <label>{{ _lang('Primary Color') }}</label>
                        <input type="color" class="form-control form-control-color" name="primary_color"
                            value="{{ get_option('primary_color') ?: '#0d6efd' }}">
                    </div>
                    <div class="col-lg-3">
                        <label>{{ _lang('Secondary Color') }}</label>
                        <input type="color" class="form-control form-control-color" name="secondary_color"
                            value="{{ get_option('secondary_color') ?: '#6610f2' }}">
                    </div>
                    <div class="col-lg-3">
                        <label>{{ _lang('Text Color') }}</label>
                        <input type="color" class="form-control form-control-color" name="text_color"
                            value="{{ get_option('text_color') ?: '#000000' }}">
                    </div>
                    <div class="col-lg-3">
                        <label>{{ _lang('Sidebar Color') }}</label>
                        <input type="color" class="form-control form-control-color" name="sidebar_color"
                            value="{{ get_option('sidebar_color') ?: '#212529' }}">
                    </div>
                    <div class="col-lg-12 mt-4">
                        <button class="btn btn-light-primary">
                            {{ _lang('Save Legacy Settings') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        // Keep every color-picker + hex/rgb text pair in sync, either direction.
        document.querySelectorAll('[data-text-target]').forEach(function (picker) {
            picker.addEventListener('input', function () {
                var text = document.getElementById(picker.dataset.textTarget);
                if (text) text.value = picker.value;
                updatePreview(picker);
            });
        });
        document.querySelectorAll('[data-picker-target]').forEach(function (text) {
            text.addEventListener('input', function () {
                var picker = document.getElementById(text.dataset.pickerTarget);
                // Native <input type="color"> only accepts strict #rrggbb - if the
                // typed value matches that, sync the swatch too; rgb()/other
                // formats still save fine, just won't drive the swatch preview.
                if (picker && /^#([0-9A-Fa-f]{6})$/.test(text.value.trim())) {
                    picker.value = text.value.trim();
                }
                updatePreview(text);
            });
        });

        function updatePreview(el) {
            var slotKey = (el.dataset.textTarget || el.dataset.pickerTarget || '').replace(/_color2?_(picker|text)$/, '');
            if (!slotKey) return;
            var preview = document.getElementById(slotKey + '_preview');
            if (!preview) return;
            var typeSelect = document.querySelector('select[data-slot="' + slotKey + '"]');
            var color1 = document.getElementById(slotKey + '_color_text');
            var color2 = document.getElementById(slotKey + '_color2_text');
            if (!typeSelect || !color1) return;
            preview.style.background = typeSelect.value === 'gradient' && color2
                ? 'linear-gradient(135deg, ' + color1.value + ', ' + color2.value + ')'
                : color1.value;
        }

        // Solid <-> Gradient toggle per slot
        document.querySelectorAll('.color-type-select').forEach(function (select) {
            select.addEventListener('change', function () {
                var slot = select.dataset.slot;
                var row = document.getElementById(slot + '_gradient_row');
                if (row) row.style.display = select.value === 'gradient' ? '' : 'none';
                updatePreview(select.closest('.border').querySelector('[data-text-target]'));
            });
        });
    })();
</script>