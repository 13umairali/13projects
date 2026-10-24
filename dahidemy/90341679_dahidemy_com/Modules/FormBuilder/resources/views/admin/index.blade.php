@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Dynamic Forms') }}
@endsection

@section('create-button')
    <a href="{{ route('formbuilder.forms.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Create New Form') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="alert alert-light-info d-flex align-items-center mb-5">
                <i class="fa fa-info-circle fs-3 me-3"></i>
                <div>
                    {{ _lang('Build any type of form — student registration, job application, feedback, or anything custom — add your own fields, publish it, and optionally show it as a card on your public homepage.') }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Type') }}</th>
                            <th>{{ _lang('Fields') }}</th>
                            <th>{{ _lang('Submissions') }}</th>
                            <th>{{ _lang('On Homepage') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($forms as $form)
                            <tr>
                                <td>{{ $form->id }}</td>
                                <td>
                                    <a href="{{ route('formbuilder.forms.edit', $form->id) }}" class="fw-bold text-dark text-hover-primary">
                                        {{ $form->name }}
                                    </a>
                                    <br>
                                    <span class="text-muted fs-8">/forms/{{ $form->slug }}</span>
                                </td>
                                <td>{{ ucfirst(str_replace('_', ' ', $form->type)) }}</td>
                                <td>{{ $form->fields()->count() }}</td>
                                <td>
                                    <a href="{{ route('formbuilder.forms.submissions', $form->id) }}" class="badge badge-light-primary">
                                        {{ $form->submissions_count }} {{ _lang('view') }}
                                    </a>
                                </td>
                                <td>
                                    <form action="{{ route('formbuilder.forms.toggle-homepage', $form->id) }}" method="POST"
                                        onsubmit="return confirm('{{ $form->show_on_homepage ? _lang('Remove this form from the homepage?') : _lang('Show this form as a card on the homepage?') }}')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn btn-sm {{ $form->show_on_homepage ? 'btn-light-success' : 'btn-light-secondary' }}"
                                            title="{{ _lang('Click to toggle') }}">
                                            <i class="fa {{ $form->show_on_homepage ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                            {{ $form->show_on_homepage ? _lang('Visible') : _lang('Hidden') }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form action="{{ route('formbuilder.forms.toggle-status', $form->id) }}" method="POST"
                                        onsubmit="return confirm('{{ $form->isPublished() ? _lang('Move this form back to Draft? It will disappear from the public site.') : _lang('Publish this form? It will become publicly visible.') }}')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="btn btn-sm {{ $form->isPublished() ? 'btn-light-success' : 'btn-warning' }}"
                                            title="{{ _lang('Click to toggle') }}">
                                            <i class="fa {{ $form->isPublished() ? 'fa-toggle-on' : 'fa-toggle-off' }} me-1"></i>
                                            {{ $form->isPublished() ? _lang('Published') : _lang('Draft — click to publish') }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('formbuilder.forms.edit', $form->id) }}" class="btn btn-sm btn-icon btn-light-primary" title="{{ _lang('Edit') }}">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                    @if ($form->isPublished())
                                        <a href="{{ route('public-form.show', $form->slug) }}" target="_blank" class="btn btn-sm btn-icon btn-light-info" title="{{ _lang('Preview') }}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    @endif
                                    <form action="{{ route('formbuilder.forms.destroy', $form->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ _lang('Delete this form? This cannot be undone.') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-light-danger" title="{{ _lang('Delete') }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-muted">
                                    {{ _lang('No forms yet. Create your first one to get started.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $forms->links() }}
            </div>
        </div>
    </div>
@endsection
