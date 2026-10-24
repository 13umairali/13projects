@extends('central.super.layout')

@section('title', __('central.ImpersonationSession', ['id' => $log->id]))

@section('content')
<div class="breadcrumb-custom">
    <a href="{{ route('super.impersonation-logs.index') }}">{{ __('central.ImpersonationLogs') }}</a>
    <span class="separator"><i class="bi bi-chevron-right"></i></span>
    <span class="current">#{{ $log->id }}</span>
</div>

<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h1>{{ __('central.ImpersonationSession', ['id' => $log->id]) }}</h1>
        <p class="page-subtitle">{{ __('central.ImpersonationSessionSubtitle') }}</p>
    </div>
    <a href="{{ route('super.impersonation-logs.index') }}" class="btn btn-outline btn-sm">
        <i class="bi bi-arrow-left"></i> {{ __('central.ImpersonationBackToLogs') }}
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="content-card mb-4">
            <div class="card-body-custom">
                <h5 class="mb-3">{{ __('central.ImpersonationDetails') }}</h5>
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">{{ __('central.ImpersonationAdmin') }}</td>
                            <td class="fw-600">{{ $log->admin_name }} <span class="text-muted fs-xs3">{{ $log->admin_email }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('central.ImpersonationTargetUser') }}</td>
                            <td class="fw-600">{{ $log->user_name ?: ('#' . $log->user_id) }} <span class="text-muted fs-xs3">{{ $log->user_email }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('central.ImpersonationTenant') }}</td>
                            <td>
                                <a href="{{ route('super.tenants.users', $log->tenant_id) }}">{{ $log->tenant_name ?: $log->tenant_id }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('central.ImpersonationIp') }}</td>
                            <td>{{ $log->ip_address ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('central.ImpersonationUserAgent') }}</td>
                            <td class="fs-xs3 text-muted">{{ $log->user_agent ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('central.StatusCol') }}</td>
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
                                @if($log->ended_by)
                                    <span class="text-muted fs-xs3 ms-1">({{ __('central.ImpersonationEndedBy_' . $log->ended_by) }})</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('central.ImpersonationRequestedAt') }}</td>
                            <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('central.ImpersonationStartedAt') }}</td>
                            <td>{{ $log->started_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('central.ImpersonationEndedAt') }}</td>
                            <td>{{ $log->ended_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('central.ImpersonationLastActivity') }}</td>
                            <td>{{ $log->last_activity_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="content-card">
            <div class="card-body-custom p-0">
                <div class="d-flex align-items-center justify-content-between px-3 pt-3">
                    <h5 class="mb-2">{{ __('central.ImpersonationActions') }}</h5>
                    <span class="text-muted fs-xs3">{{ __('central.ImpersonationActionsHint') }}</span>
                </div>
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('central.ImpersonationActionTime') }}</th>
                            <th>{{ __('central.ImpersonationActionMethod') }}</th>
                            <th>{{ __('central.ImpersonationActionPath') }}</th>
                            <th>{{ __('central.StatusCol') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($log->actions ?? [] as $action)
                        <tr>
                            <td class="text-muted fs-sm4">{{ $action['t'] ?? '' }}</td>
                            <td><span class="badge badge-trial">{{ $action['method'] ?? '' }}</span></td>
                            <td class="fs-sm4" style="word-break: break-all;">{{ $action['path'] ?? '' }}</td>
                            <td class="text-muted fs-sm4">{{ $action['status'] ?? '' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">{{ __('central.NoImpersonationActions') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
