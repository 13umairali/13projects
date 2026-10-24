<form method="get">
    @csrf

    <div class="row">

        {{-- CLASS --}}
        <div class="col-sm-3">
            <div class="form-group">
                <label class="control-label mb-2">
                    {{ _lang('Class') }}
                </label>

                <select name="class_id" id="class_id_select" class="form-select form-select-sm form-select-solid"
                    data-control="select2">

                    <option value="">{{ _lang('All Classes') }}</option>

                    {!! create_option('classes', 'id', 'class_name', request()->class_id) !!}

                </select>
            </div>
        </div>

        {{-- SECTION --}}
        <div class="col-sm-3">
            <div class="form-group">
                <label class="control-label mb-2">
                    {{ _lang('Section') }}
                </label>

                <select name="section_id" id="section_id_select" class="form-select form-select-sm form-select-solid"
                    data-control="select2">

                    <option value="">
                        Select Section
                    </option>

                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}"
                            {{ request()->section_id == $section->id ? 'selected' : '' }}>
                            {{ $section->section_name }}
                        </option>
                    @endforeach

                </select>
            </div>
        </div>

        {{-- FREE-TEXT SEARCH: Name, Admission No, Roll No, National ID --}}
        {{-- Names can be duplicated across a school, so this lets staff find --}}
        {{-- the exact student by a unique identifier instead. --}}
        <div class="col-sm-4">
            <div class="form-group">
                <label class="control-label mb-2">
                    {{ _lang('Search') }}
                </label>

                <input type="text" name="search" id="student_search_input"
                    class="form-control form-control-sm"
                    placeholder="{{ _lang('Name, Admission No, Roll No or National ID') }}"
                    value="{{ request()->search }}">
            </div>
        </div>

        {{-- BUTTON --}}
        <div class="col-sm-2">
            <div class="form-group">
                <button type="submit" class="btn btn-primary margin_top_24">
                    {{ _lang('Search') }}
                </button>
            </div>
        </div>

    </div>
</form>
