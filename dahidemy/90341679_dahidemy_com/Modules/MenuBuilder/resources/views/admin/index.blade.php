@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Website Menu') }}
@endsection

@section('create-button')
    <a href="{{ route('menubuilder.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add Menu Item') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="alert alert-light-info d-flex align-items-center mb-5">
                <i class="fa fa-info-circle fs-3 me-3"></i>
                <div>
                    {{ _lang('This controls the navigation menu on your public website. Items without any children show as a plain link; items with children show as a dropdown. Use the up/down arrows to reorder.') }}
                </div>
            </div>

            @if ($items->isEmpty())
                <div class="text-center py-15">
                    <p class="text-muted mb-4">{{ _lang("You haven't customized your menu yet — your website is showing its default navigation.") }}</p>
                    <form action="{{ route('menubuilder.reset-to-defaults') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-magic me-1"></i> {{ _lang('Start From the Default Menu') }}
                        </button>
                    </form>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-4">
                        <thead>
                            <tr>
                                <th style="width: 90px">{{ _lang('Order') }}</th>
                                <th>{{ _lang('Label') }}</th>
                                <th>{{ _lang('Links To') }}</th>
                                <th>{{ _lang('Status') }}</th>
                                <th>{{ _lang('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <form action="{{ route('menubuilder.move-up', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-icon btn-light"><i class="fa fa-arrow-up"></i></button>
                                            </form>
                                            <form action="{{ route('menubuilder.move-down', $item->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-icon btn-light"><i class="fa fa-arrow-down"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                    <td><strong>{{ $item->label }}</strong></td>
                                    <td>
                                        @if ($item->link_type === 'academics_pages')
                                            <span class="text-muted">{{ _lang('Auto dropdown of your Pages') }}</span>
                                        @else
                                            <span class="text-muted">{{ \Modules\MenuBuilder\Models\CustomMenu::ROUTE_OPTIONS[$item->link_value] ?? $item->link_value ?? '' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('menubuilder.toggle-status', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="badge {{ $item->isActive() ? 'badge-light-success' : 'badge-light-secondary' }} border-0">
                                                {{ $item->isActive() ? _lang('Active') : _lang('Inactive') }}
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <a href="{{ route('menubuilder.edit', $item->id) }}" class="btn btn-sm btn-icon btn-light-primary"><i class="fa fa-pen"></i></a>
                                        <form action="{{ route('menubuilder.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this menu item and any of its dropdown children?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>

                                @foreach ($item->children as $child)
                                    <tr class="bg-light-subtle">
                                        <td>
                                            <div class="d-flex gap-1 ps-6">
                                                <form action="{{ route('menubuilder.move-up', $child->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-light"><i class="fa fa-arrow-up"></i></button>
                                                </form>
                                                <form action="{{ route('menubuilder.move-down', $child->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-light"><i class="fa fa-arrow-down"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="ps-6"><i class="fa fa-arrow-turn-up fa-rotate-90 text-muted me-2"></i>{{ $child->label }}</td>
                                        <td><span class="text-muted">{{ \Modules\MenuBuilder\Models\CustomMenu::ROUTE_OPTIONS[$child->link_value] ?? $child->link_value ?? '' }}</span></td>
                                        <td>
                                            <form action="{{ route('menubuilder.toggle-status', $child->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="badge {{ $child->isActive() ? 'badge-light-success' : 'badge-light-secondary' }} border-0">
                                                    {{ $child->isActive() ? _lang('Active') : _lang('Inactive') }}
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="{{ route('menubuilder.edit', $child->id) }}" class="btn btn-sm btn-icon btn-light-primary"><i class="fa fa-pen"></i></a>
                                            <form action="{{ route('menubuilder.destroy', $child->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this menu item?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-light-danger"><i class="fa fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
