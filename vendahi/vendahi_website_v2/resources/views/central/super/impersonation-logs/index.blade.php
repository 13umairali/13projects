@extends('central.super.layout')

@section('title', __('central.ImpersonationLogs'))

@section('content')
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1>{{ __('central.ImpersonationLogs') }}</h1>
        <p class="page-subtitle">{{ __('central.ImpersonationLogsSubtitle') }}</p>
    </div>
</div>

{{-- Filters --}}
<div class="content-card mb-4">
    <div class="card-body-custom py-3">
        <form method="GET" action="{{ route('super.impersonation-logs.index') }}" class="d-flex flex-wrap align-items-center gap-3">
            <div class="d-flex align-items-center gap-2 flex-grow-1 max-w-320">
                <div class="input-icon-wrapper">
                    <i class="bi bi-search input-icon"></i>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="{{ __('central.ImpersonationSearchPlaceholder') }}" value="{{ request('search') }}">
                </div>
            </div>
            <select name="status" class="form-select form-select-sm filter-select">
                <option value="">{{ __('central.AllStatus') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('central.Active') }}</option>
                <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>{{ __('central.ImpersonationEnded') }}</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>{{ __('central.ImpersonationExpired') }}</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('central.ImpersonationFailed') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('central.ImpersonationPending') }}</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel"></i> {{ __('central.Filter') }}
            </button>
            @if(request('search') || (request('status') !== null && request('status') !== ''))
                <a href="{{ route('super.impersonation-logs.index') }}" class="btn btn-ghost btn-sm">{{ __('central.Clear') }}</a>
            @endif
        </form>
    </div>
</div>

{{-- Logs table --}}
<div class="content-card">
    <div class="card-body-custom p-0">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th class="w-50px">#</th>
                    <th>{{ __('central.ImpersonationAdmin') }}</th>
                    <th>{{ __('central.ImpersonationTargetUser') }}</th>
                    <th>{{ __('central.ImpersonationTenant') }}</th>
                    <th>{{ __('central.ImpersonationIp') }}</th>
                    <th>{{ __('central.ImpersonationStartedAt') }}</th>
                    <th>{{ __('central.ImpersonationEndedAt') }}</th>
                    <th>{{ __('central.StatusCol') }}</th>
                    <th class="action-cell">{{ __('central.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td><span class="fw-600 text-muted">{{ $log->id }}</span></td>
                    <td>
                        <span class="fw-700 fs-md">{{ $log->admin_name }}</span>
                        <div class="text-muted fs-xs3">{{ $log->admin_email }}</div>
                    </td>
                    <td>
                        <span class="fw-600">{{ $log->user_name ?: '—' }}</span>
                        <div class="text-muted fs-xs3">{{ $log->user_email }}</div>
                    </td>
                    <td>
                        <span class="text-muted fs-sm4">{{ $log->tenant_name ?: $log->tenant_id }}</span>
                    </td>
                    <td><span class="text-muted fs-sm4">{{ $log->ip_address ?: '—' }}</span></td>
                    <td><span class="text-muted fs-sm4">{{ $log->started_at?->format('Y-m-d H:i:s') ?? '—' }}</span></td>
                    <td><span class="text-muted fs-sm4">{{ $log->ended_at?->format('Y-m-d H:i:s') ?? '—' }}</span></td>
                    <td>
                        @if($log->status === \App\Models\Central\ImpersonationLog::STATUS_ACTIVE)
                            <span class="badge badge-active">{{ __('central.Active') }}</span>
                        @elseif($log->status === \App\Models\Central\ImpersonationLog::STATUS_ENDED)
                            <span class="badge badge-trial">{{ __('central.ImpersonationEnded') }}</span>
                        @elseif($log->status === \App\Models\Central\ImpersonationLog::STATUS_PENDING)
                            <span class="badge badge-pending">{{ __('central.ImpersonationPending') }}</span>
                        @else
                            <span class="badge badge-cancelled">{{ __('central.Impersonation' . ucfirst($log->status)) }}</span>
                        @endif
                    </td>
                    <td class="action-cell">
                        <a href="{{ route('super.impersonation-logs.show', $log) }}" class="btn btn-outline btn-sm">
                            <i class="bi bi-eye"></i> {{ __('central.ImpersonationDetails') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bi bi-incognito fs-xl3"></i>
                        <p class="mt-2 mb-0">{{ __('central.NoImpersonationLogs') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="p-3">{{ $logs->links('vendor.pagination.bootstrap-5') }}</div>
    @endif
</div>
@endsection
