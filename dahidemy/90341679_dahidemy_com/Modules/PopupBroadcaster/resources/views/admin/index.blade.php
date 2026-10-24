@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Website Popups') }}
@endsection

@section('create-button')
    <a href="{{ route('popupbroadcaster.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add Popup') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="alert alert-light-info d-flex align-items-center mb-5">
                <i class="fa fa-info-circle fs-3 me-3"></i>
                <div>
                    {{ _lang('Popups appear on your public website — great for exam reminders, admission deadlines, or class start announcements. Each visitor only sees an active popup according to its display frequency setting.') }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>{{ _lang('Title') }}</th>
                            <th>{{ _lang('Audience') }}</th>
                            <th>{{ _lang('Schedule') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($popups as $popup)
                            <tr>
                                <td><strong>{{ $popup->title }}</strong></td>
                                <td>{{ \Modules\PopupBroadcaster\Models\Popup::AUDIENCE_OPTIONS[$popup->audience] ?? $popup->audience }}</td>
                                <td class="fs-8 text-muted">
                                    @if ($popup->start_at || $popup->end_at)
                                        {{ optional($popup->start_at)->format('d M Y') ?: _lang('Now') }}
                                        &rarr;
                                        {{ optional($popup->end_at)->format('d M Y') ?: _lang('Forever') }}
                                    @else
                                        {{ _lang('Always active') }}
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('popupbroadcaster.toggle-status', $popup->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="badge {{ $popup->isActive() ? 'badge-light-success' : 'badge-light-secondary' }} border-0">
                                            {{ $popup->isActive() ? _lang('Active') : _lang('Inactive') }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('popupbroadcaster.edit', $popup->id) }}" class="btn btn-sm btn-icon btn-light-primary"><i class="fa fa-pen"></i></a>
                                    <form action="{{ route('popupbroadcaster.destroy', $popup->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this popup?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-10 text-muted">{{ _lang('No popups yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $popups->links() }}
            </div>
        </div>
    </div>
@endsection
