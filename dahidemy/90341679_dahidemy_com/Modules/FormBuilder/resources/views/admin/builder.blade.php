@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $form->exists ? _lang('Edit Form') : _lang('Create Form') }}
@endsection

@section('create-button')
    <a href="{{ route('formbuilder.forms.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Forms') }}
    </a>
@endsection

@section('content')
    <div class="row"
        x-data="formBuilder({{ $fields->map(fn ($f) => [
            'label' => $f->label,
            'field_key' => $f->field_key,
            'type' => $f->type,
            'placeholder' => $f->placeholder,
            'options' => $f->options ? implode(', ', $f->options) : '',
            'is_required' => $f->is_required,
            'width' => $f->width,
        ])->values()->toJson() }})"
        x-init="init()">

        <div class="col-lg-12">
            <form method="POST"
                action="{{ $form->exists ? route('formbuilder.forms.update', $form->id) : route('formbuilder.forms.store') }}"
                @submit="fieldsJson = JSON.stringify(fields)">
                @csrf
                @if ($form->exists)
                    @method('PUT')
                @endif

                <input type="hidden" name="fields_json" x-model="fieldsJson">

                {{-- ===================== FORM SETTINGS ===================== --}}
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">{{ _lang('Form Settings') }}</h3>
                    </div>
                    <div class="card-body row g-5">
                        <div class="col-md-6">
                            <label class="form-label required">{{ _lang('Form Name') }}</label>
                            <input type="text" name="name" class="form-control" required
                                value="{{ old('name', $form->name) }}" placeholder="e.g. Student Admission Form">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label required">{{ _lang('Form Type') }}</label>
                            <select name="type" class="form-select" required>
                                @foreach (['registration' => 'Registration', 'job_application' => 'Job Application', 'admission' => 'Admission', 'feedback' => 'Feedback', 'custom' => 'Custom'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', $form->type) == $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ _lang('Description') }}</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Shown at the top of the public form">{{ old('description', $form->description) }}</textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ _lang('Submit Button Text') }}</label>
                            <input type="text" name="submit_button_text" class="form-control"
                                value="{{ old('submit_button_text', $form->submit_button_text ?: 'Submit') }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ _lang('Notify Email') }}</label>
                            <input type="email" name="notify_email" class="form-control"
                                value="{{ old('notify_email', $form->notify_email) }}" placeholder="admin@school.com">
                            <div class="form-text">{{ _lang('Optional — get an email every time someone submits.') }}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">{{ _lang('Icon (Font Awesome class)') }}</label>
                            <input type="text" name="icon" class="form-control"
                                value="{{ old('icon', $form->icon) }}" placeholder="fa-solid fa-user-graduate">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ _lang('Success Message') }}</label>
                            <textarea name="success_message" class="form-control" rows="2">{{ old('success_message', $form->success_message ?: 'Thank you! Your submission has been received.') }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <div class="p-4 rounded border border-2 h-100 status-toggle-card {{ $form->exists && $form->isPublished() ? 'border-success bg-light-success' : 'border-secondary bg-light' }}"
                                id="statusCard">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" name="status" value="1" id="statusToggle"
                                        onchange="updateToggleCard('statusCard', 'statusCaption', this.checked, 'Live at {{ url('/forms/' . ($form->slug ?: '...')) }}', 'Currently in Draft — not visible to visitors')"
                                        {{ old('status', $form->exists ? $form->isPublished() : false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold fs-5" for="statusToggle">
                                        {{ _lang('Live') }}
                                    </label>
                                </div>
                                <p class="text-muted fs-8 mb-0" id="statusCaption">
                                    @if ($form->exists && $form->isPublished())
                                        {{ _lang('Live at') }} {{ url('/forms/' . $form->slug) }}
                                    @else
                                        {{ _lang('Currently in Draft — not visible to visitors') }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-4 rounded border border-2 h-100 status-toggle-card {{ $form->exists && $form->show_on_homepage ? 'border-success bg-light-success' : 'border-secondary bg-light' }}"
                                id="homepageCard">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" name="show_on_homepage" value="1" id="showOnHomepage"
                                        onchange="updateToggleCard('homepageCard', null, this.checked)"
                                        {{ old('show_on_homepage', $form->show_on_homepage) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold fs-5" for="showOnHomepage">
                                        {{ _lang('Show on Homepage') }}
                                    </label>
                                </div>
                                <p class="text-muted fs-8 mb-0">{{ _lang('Shown as a card on the homepage') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===================== FIELD BUILDER ===================== --}}
                <div class="card mb-6">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">{{ _lang('Form Fields') }}</h3>
                        <button type="button" class="btn btn-sm btn-light-primary" @click="addField()">
                            <i class="fa fa-plus me-1"></i> {{ _lang('Add Field') }}
                        </button>
                    </div>

                    <div class="card-body">
                        <template x-if="fields.length === 0">
                            <div class="text-center text-muted py-10">
                                {{ _lang('No fields yet. Click "Add Field" to build your form.') }}
                            </div>
                        </template>

                        <template x-for="(field, index) in fields" :key="index">
                            <div class="border rounded-3 p-4 mb-4 bg-light-subtle">
                                <div class="row g-4 align-items-start">

                                    <div class="col-md-3">
                                        <label class="form-label fs-8 text-muted mb-1">{{ _lang('Label') }}</label>
                                        <input type="text" class="form-control form-control-sm" x-model="field.label"
                                            @input="if (!field.key_touched) field.field_key = slugify(field.label)" placeholder="Full Name">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fs-8 text-muted mb-1">{{ _lang('Field Key') }}</label>
                                        <input type="text" class="form-control form-control-sm" x-model="field.field_key"
                                            @input="field.key_touched = true" placeholder="full_name">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fs-8 text-muted mb-1">{{ _lang('Type') }}</label>
                                        <select class="form-select form-select-sm" x-model="field.type">
                                            @foreach ($fieldTypes as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label fs-8 text-muted mb-1">{{ _lang('Placeholder') }}</label>
                                        <input type="text" class="form-control form-control-sm" x-model="field.placeholder">
                                    </div>

                                    <div class="col-md-1">
                                        <label class="form-label fs-8 text-muted mb-1">{{ _lang('Width') }}</label>
                                        <select class="form-select form-select-sm" x-model="field.width">
                                            <option value="full">{{ _lang('Full') }}</option>
                                            <option value="half">{{ _lang('Half') }}</option>
                                        </select>
                                    </div>

                                    <div class="col-md-1">
                                        <label class="form-label fs-8 text-muted mb-1 d-block">{{ _lang('Required') }}</label>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" x-model="field.is_required">
                                        </div>
                                    </div>

                                    <div class="col-md-1 text-end">
                                        <label class="form-label fs-8 text-muted mb-1 d-block">&nbsp;</label>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-icon btn-light" @click="moveUp(index)" :disabled="index === 0" title="{{ _lang('Move Up') }}">
                                                <i class="fa fa-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-icon btn-light" @click="moveDown(index)" :disabled="index === fields.length - 1" title="{{ _lang('Move Down') }}">
                                                <i class="fa fa-arrow-down"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-icon btn-light-danger" @click="removeField(index)" title="{{ _lang('Remove') }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-12" x-show="['select', 'radio', 'checkbox'].includes(field.type)">
                                        <label class="form-label fs-8 text-muted mb-1">{{ _lang('Choices (comma separated)') }}</label>
                                        <input type="text" class="form-control form-control-sm" x-model="field.options" placeholder="Option A, Option B, Option C">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-1"></i> {{ $form->exists ? _lang('Update Form') : _lang('Save Form') }}
                </button>

                @if ($form->exists)
                    <a href="{{ route('formbuilder.forms.submissions', $form->id) }}" class="btn btn-light-primary ms-2">
                        <i class="fa fa-inbox me-1"></i> {{ _lang('View Submissions') }}
                    </a>
                    <a href="{{ route('public-form.show', $form->slug) }}" target="_blank" class="btn btn-light-info ms-2">
                        <i class="fa fa-eye me-1"></i> {{ _lang('Preview') }}
                    </a>
                @endif
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Instantly flips a toggle card's border/background between the
        // "on" (green) and "off" (grey) look the moment it's clicked -
        // no page reload needed to see the change take effect.
        function updateToggleCard(cardId, captionId, isOn, onText, offText) {
            const card = document.getElementById(cardId);
            if (isOn) {
                card.classList.remove('border-secondary', 'bg-light');
                card.classList.add('border-success', 'bg-light-success');
            } else {
                card.classList.remove('border-success', 'bg-light-success');
                card.classList.add('border-secondary', 'bg-light');
            }

            if (captionId) {
                document.getElementById(captionId).textContent = isOn ? onText : offText;
            }
        }

        function formBuilder(initialFields) {
            return {
                fields: [],
                fieldsJson: '',
                init() {
                    this.fields = (initialFields || []).map((f) => ({ ...f, key_touched: true }));
                },
                addField() {
                    this.fields.push({
                        label: '', field_key: '', type: 'text', placeholder: '',
                        options: '', is_required: false, width: 'full', key_touched: false,
                    });
                },
                removeField(index) {
                    this.fields.splice(index, 1);
                },
                moveUp(index) {
                    if (index === 0) return;
                    [this.fields[index - 1], this.fields[index]] = [this.fields[index], this.fields[index - 1]];
                },
                moveDown(index) {
                    if (index === this.fields.length - 1) return;
                    [this.fields[index + 1], this.fields[index]] = [this.fields[index], this.fields[index + 1]];
                },
                slugify(text) {
                    return (text || '').toString().toLowerCase().trim()
                        .replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
                },
            };
        }
    </script>
@endsection
