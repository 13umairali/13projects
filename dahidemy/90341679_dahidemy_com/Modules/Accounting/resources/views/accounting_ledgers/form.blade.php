@php
    $route = isset($id) ? route('accounting-ledgers.update', $accountingLedger->id) : route('accounting-ledgers.store');
    $accountingLedger = isset($accountingLedger) ? $accountingLedger : null;
@endphp

<div class="row">
    <div class="col-lg-12">
        <form action="{{ $route }}" method="post">
            @csrf

            @isset($id)
                @method('PATCH')
            @endisset

            <div class="form-group">
                <label class="control-label my-2">{{ _lang('Category') }}</label>

                <select class="form-select form-select-sm form-select-solid" data-control="select2"
                    name="accounting_category_id" id="accounting_category_select">
                    <option value="">--Select Category--</option>
                    @foreach ($accountingCategories as $accountingCategory)
                        <option
                            @isset($id)
                         {{ $accountingCategory->id === $accountingLedger->accounting_category_id ? 'selected' : '' }}
                        @endisset
                            value="{{ $accountingCategory->id }}">
                            {{ $accountingCategory->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="control-label my-2">{{ _lang('Group') }}</label>

                <select name="accounting_group_id" id="accounting_group_select"
                    class="form-select form-select-sm form-select-solid" data-control="select2">
                    @isset($id)
                        <option value="{{ $accountingCategory->accounting_group_id ?? '' }}">
                            {{ $accountingLedger->accountingGroup->name ?? '' }}
                        </option>
                    @endisset

                    <option value="">--Select Group--</option>
                    <!-- Account groups will be dynamically loaded here -->
                </select>
            </div>

            <div class="form-group">
                <label class="control-label my-2">{{ _lang('Ledger Name') }}</label>
                <input type="text" class="form-control" name="ledger_name" required
                    value="{{ get_old_value($accountingLedger, 'ledger_name') }}" placeholder="Ledger Name">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary mt-5">{{ _lang('Save') }}</button>
            </div>
        </form>
    </div>
</div>

@section('js-script')
    <script>
        // Add JavaScript to handle the change event of the accounting category dropdown
        $(document).ready(function() {
            $('#accounting_category_select').on('change', function() {
                var categoryId = $(this).val();
                console.log(categoryId)

                // Make an AJAX request to fetch account groups based on the selected category
                $.ajax({
                    url: '/get-account-groups/' + categoryId,
                    type: 'GET',
                    success: function(data) {
                        // Update the account groups dropdown with the fetched data
                        var groupSelect = $('#accounting_group_select');
                        groupSelect.empty().append(
                            '<option value="">--Select Group--</option>');

                        $.each(data, function(key, value) {
                            groupSelect.append('<option value="' + key + '">' + value +
                                '</option>');
                        });
                    }
                });
            });
        });
    </script>
@endsection
