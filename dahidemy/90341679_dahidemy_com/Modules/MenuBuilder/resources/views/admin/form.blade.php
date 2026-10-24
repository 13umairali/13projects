@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ $item->exists ? _lang('Edit Menu Item') : _lang('Add Menu Item') }}
@endsection

@section('create-button')
    <a href="{{ route('menubuilder.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('All Menu Items') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST"
                        action="{{ $item->exists ? route('menubuilder.update', $item->id) : route('menubuilder.store') }}">
                        @csrf
                        @if ($item->exists)
                            @method('PUT')
                        @endif

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Label') }}</label>
                            <input type="text" name="label" class="form-control" required
                                value="{{ old('label', $item->label) }}" placeholder="e.g. About Us">
                        </div>

                        <div class="mb-5">
                            <label class="form-label">{{ _lang('Show as a dropdown under') }}</label>
                            <select name="parent_id" class="form-select">
                                <option value="">{{ _lang('— Top-level item —') }}</option>
                                @foreach ($parents as $parent)
                                    <option value="{{ $parent->id }}" @selected(old('parent_id', $item->parent_id) == $parent->id)>
                                        {{ $parent->label }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ _lang('Leave as top-level for a normal menu item, or nest it under another item to create a dropdown.') }}</div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label required">{{ _lang('Link Type') }}</label>
                            <select name="link_type" id="linkType" class="form-select" onchange="toggleLinkFields()">
                                <option value="custom_url" @selected(old('link_type', $item->link_type) == 'custom_url')>{{ _lang('Custom URL') }}</option>
                                <option value="home" @selected(old('link_type', $item->link_type) == 'home')>{{ _lang('Homepage') }}</option>
                                <option value="anchor" @selected(old('link_type', $item->link_type) == 'anchor')>{{ _lang('A Section on the Homepage') }}</option>
                                <option value="route" @selected(old('link_type', $item->link_type) == 'route')>{{ _lang('A Built-in Page') }}</option>
                                <option value="academics_pages" @selected(old('link_type', $item->link_type) == 'academics_pages')>{{ _lang('Auto Dropdown of My Pages') }}</option>
                            </select>
                        </div>

                        <div class="mb-5" id="anchorField" style="display:none">
                            <label class="form-label">{{ _lang('Section') }}</label>
                            <select name="anchor_value" class="form-select">
                                @foreach (\Modules\MenuBuilder\Models\CustomMenu::ANCHOR_OPTIONS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('anchor_value', $item->link_type === 'anchor' ? $item->link_value : null) == $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5" id="routeField" style="display:none">
                            <label class="form-label">{{ _lang('Page') }}</label>
                            <select name="route_value" class="form-select">
                                @foreach (\Modules\MenuBuilder\Models\CustomMenu::ROUTE_OPTIONS as $value => $label)
                                    <option value="{{ $value }}" @selected(old('route_value', $item->link_type === 'route' ? $item->link_value : null) == $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-5" id="customUrlField" style="display:none">
                            <label class="form-label">{{ _lang('URL') }}</label>
                            <input type="text" name="custom_url_value" class="form-control"
                                value="{{ old('custom_url_value', $item->link_type === 'custom_url' ? $item->link_value : null) }}"
                                placeholder="https://example.com or /page/some-page">
                            <div class="form-text">{{ _lang('A full web address, or a path on your own site starting with /') }}</div>
                        </div>

                        <div class="mb-5">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="open_new_tab" value="1" id="openNewTab"
                                    {{ old('open_new_tab', $item->open_new_tab) ? 'checked' : '' }}>
                                <label class="form-check-label" for="openNewTab">{{ _lang('Open in a new browser tab') }}</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> {{ $item->exists ? _lang('Update Menu Item') : _lang('Add Menu Item') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function toggleLinkFields() {
            const type = document.getElementById('linkType').value;
            document.getElementById('anchorField').style.display = type === 'anchor' ? 'block' : 'none';
            document.getElementById('routeField').style.display = type === 'route' ? 'block' : 'none';
            document.getElementById('customUrlField').style.display = type === 'custom_url' ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', toggleLinkFields);
    </script>
@endsection
