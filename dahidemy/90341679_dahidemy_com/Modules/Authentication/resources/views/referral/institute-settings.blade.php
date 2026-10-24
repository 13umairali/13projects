@extends('layouts.backend')

@section('page_title_breadcrumb')
    Referral Reward Settings
@endsection

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-light-info d-flex align-items-center mb-5">
        <i class="fa fa-info-circle fs-3 me-3"></i>
        <div>
            Decide what a role in YOUR institute earns for two separate things: referring a brand new institute to
            the platform (in addition to whatever the SaaS admin also gives), or referring a new person to join
            YOUR institute - e.g. a student referring another student. To review and approve referrals your people
            have actually made, see <a href="{{ route('institute-referrals.index') }}">Institute Referrals</a> and
            <a href="{{ route('internal-referrals.index') }}">Member Referrals</a>.
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header">
            <h5>Member Referrals</h5>
        </div>
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <p class="mb-1 fw-bold">Allow your students, parents, teachers and staff to refer new members</p>
                <p class="mb-0 text-muted small">
                    When enabled, a "Referral code" field appears on your institute's admission and staff-creation
                    forms. When disabled, that field is hidden everywhere and any code entered is simply ignored -
                    it never blocks someone from being created.
                </p>
            </div>
            <form method="POST" action="{{ route('institute-referral-settings.toggle') }}">
                @csrf
                <input type="hidden" name="enabled" value="{{ $internalReferralEnabled ? '0' : '1' }}">
                <button type="submit" class="btn {{ $internalReferralEnabled ? 'btn-light-danger' : 'btn-success' }}">
                    {{ $internalReferralEnabled ? 'Disable' : 'Enable' }}
                </button>
            </form>
        </div>
    </div>

    <div class="card mb-5">
        <div class="card-header">
            <h5>Current Rules</h5>
        </div>
        <div class="card-body">
            @if ($referralSettings->isEmpty())
                <p class="text-muted mb-0">No reward rules yet - add one below for any role in your institute (student, parent, teacher, accountant, staff, or any custom role you've created).</p>
            @else
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle">
                        <thead>
                            <tr class="text-muted">
                                <th>Role</th>
                                <th>Referral Type</th>
                                <th>Reward Type</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referralSettings as $setting)
                                <tr>
                                    <form action="{{ route('institute-referral-settings.update', $setting->id) }}" method="POST" id="edit-{{ $setting->id }}">
                                        @csrf
                                        @method('PUT')
                                    </form>
                                    <td class="fw-bold text-capitalize">{{ $setting->referrer_role }}</td>
                                    <td>
                                        @if ($setting->referral_type === 'refer_member')
                                            <span class="badge bg-light-primary text-primary">Refer a New Member</span>
                                        @else
                                            <span class="badge bg-light-info text-info">Refer a New Institute</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="reward_type" form="edit-{{ $setting->id }}" class="form-select form-select-sm w-auto d-inline-block">
                                            <option value="fee_discount" {{ $setting->reward_type === 'fee_discount' ? 'selected' : '' }}>Fee Discount</option>
                                            <option value="points" {{ $setting->reward_type === 'points' ? 'selected' : '' }}>Points</option>
                                            <option value="cash_withdrawal" {{ $setting->reward_type === 'cash_withdrawal' ? 'selected' : '' }}>Cash (Withdrawable)</option>
                                        </select>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm" style="max-width: 160px;">
                                            <input type="number" step="0.01" min="0" name="reward_value" form="edit-{{ $setting->id }}"
                                                value="{{ $setting->reward_value }}" class="form-control">
                                            <span class="input-group-text">
                                                <input type="checkbox" name="is_percentage" form="edit-{{ $setting->id }}" value="1"
                                                    {{ $setting->is_percentage ? 'checked' : '' }}> %
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <select name="status" form="edit-{{ $setting->id }}" class="form-select form-select-sm w-auto d-inline-block">
                                            <option value="1" {{ $setting->status ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ ! $setting->status ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </td>
                                    <td class="text-end">
                                        <button type="submit" form="edit-{{ $setting->id }}" class="btn btn-sm btn-primary">Save</button>
                                        <form action="{{ route('institute-referral-settings.destroy', $setting->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete this reward rule?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Add a Reward Rule</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('institute-referral-settings.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Role</label>
                        <input type="text" name="referrer_role" class="form-control" placeholder="e.g. student, parent, teacher" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Referral Type</label>
                        <select name="referral_type" class="form-select">
                            <option value="refer_member">Refer a New Member (join this institute)</option>
                            <option value="refer_institute">Refer a New Institute (platform sign-up)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Reward Type</label>
                        <select name="reward_type" class="form-select">
                            <option value="fee_discount">Fee Discount</option>
                            <option value="points">Points</option>
                            <option value="cash_withdrawal">Cash (Withdrawable)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Value</label>
                        <input type="number" step="0.01" min="0" name="reward_value" class="form-control" placeholder="e.g. 10" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">%?</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_percentage" value="1" class="form-check-input">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Add Rule</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection