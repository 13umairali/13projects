@extends('layouts.backend')

@section('page_title_breadcrumb')
    Withdrawal Settings
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-light-info d-flex align-items-center mb-5">
        <i class="fa fa-info-circle fs-3 me-3"></i>
        <div>
            Optionally override the platform's own withdrawal limits and payout methods for your institute's users.
            Leave a field blank to keep using whatever the platform has set.
            <br>
            <strong>Current platform default:</strong>
            Min ${{ number_format($platform?->min_amount ?? 0, 2) }},
            Max {{ $platform?->max_amount ? '$'.number_format($platform->max_amount, 2) : 'unlimited' }},
            Methods: {{ $platform?->enabled_methods ? implode(', ', array_map(fn($m) => ucwords(str_replace('_',' ',$m)), $platform->enabled_methods)) : 'all' }}
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5>Your Institute's Override</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('institute-withdrawal-settings.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Minimum Withdrawal Amount ($)</label>
                        <input type="number" step="0.01" min="0" name="min_amount" class="form-control"
                            value="{{ old('min_amount', $setting?->min_amount) }}" placeholder="Leave blank to use platform default">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Maximum Withdrawal Amount ($)</label>
                        <input type="number" step="0.01" min="0" name="max_amount" class="form-control"
                            value="{{ old('max_amount', $setting?->max_amount) }}" placeholder="Leave blank for unlimited / platform default">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label d-block">Enabled Payout Methods</label>
                    <p class="text-muted small">Leave all unchecked to use whatever the platform allows.</p>
                    @foreach ($allMethods as $method)
                        <div class="form-check form-check-inline">
                            <input type="checkbox" name="enabled_methods[]" value="{{ $method }}" class="form-check-input"
                                id="method-{{ $method }}"
                                {{ in_array($method, old('enabled_methods', $setting?->enabled_methods ?? [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="method-{{ $method }}">
                                {{ ucwords(str_replace('_', ' ', $method)) }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary">Save Override</button>

                @if ($setting)
                    <button type="submit" form="reset-form" class="btn btn-outline-secondary">Reset to Platform Default</button>
                @endif
            </form>

            @if ($setting)
                <form id="reset-form" method="POST" action="{{ route('institute-withdrawal-settings.reset') }}" class="d-none"
                    onsubmit="return confirm('Remove your institute\'s override and fully inherit the platform default?');">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        </div>
    </div>
@endsection