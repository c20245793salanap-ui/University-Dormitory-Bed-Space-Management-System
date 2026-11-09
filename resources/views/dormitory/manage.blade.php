@extends('layouts.app')

@section('title', $definition['title'])
@section('heading', $definition['title'])

@section('content')
    <div class="manage-intro">
        <div>
            <p class="page-kicker">DORMITORY RECORDS</p>
            <h2>{{ $definition['title'] }}</h2>
            <p class="muted">Create, update, and review {{ strtolower($definition['title']) }}.</p>
        </div>
        <span class="record-count">{{ number_format($records->total()) }} {{ $records->total() === 1 ? 'record' : 'records' }}</span>
    </div>

    <div class="manage-layout">
        <section class="panel form-panel">
            <div class="panel-heading">
                <div>
                    <p class="page-kicker">{{ $editing ? 'UPDATE RECORD' : 'NEW RECORD' }}</p>
                    <h2>{{ $editing ? 'Edit '.$definition['singular'] : 'Add '.$definition['singular'] }}</h2>
                </div>
                @if ($editing)
                    <a class="icon-link" href="{{ route($definition['routePrefix'].'.index') }}" aria-label="Cancel editing">×</a>
                @endif
            </div>

            <form method="POST" action="{{ $editing ? route($definition['routePrefix'].'.update', [$definition['routeParameter'] => $editing->id]) : route($definition['routePrefix'].'.store') }}" class="record-form">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                @foreach ($definition['fields'] as $field)
                    @php
                        $fieldValue = old($field['name'], $editing?->getAttribute($field['name']));
                        $options = $formOptions[$field['name']] ?? [];

                        if ($field['type'] === 'date' && $fieldValue instanceof \DateTimeInterface) {
                            $fieldValue = $fieldValue->format('Y-m-d');
                        }
                    @endphp
                    <label class="form-field" for="{{ $field['name'] }}">
                        <span>{{ $field['label'] }} <i>*</i></span>
                        @if ($field['type'] === 'select')
                            <select id="{{ $field['name'] }}" name="{{ $field['name'] }}" required>
                                <option value="">Choose {{ strtolower($field['label']) }}</option>
                                @foreach ($options as $value => $label)
                                    <option value="{{ $value }}" @selected((string) $fieldValue === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @else
                            <input
                                id="{{ $field['name'] }}"
                                name="{{ $field['name'] }}"
                                type="{{ $field['type'] }}"
                                value="{{ $fieldValue }}"
                                @if (isset($field['step'])) step="{{ $field['step'] }}" min="0" @endif
                                    @if ($field['type'] === 'text') maxlength="{{ $field['name'] === 'student_id' ? 30 : ($field['name'] === 'bed_slot' ? 20 : 255) }}" @endif
                                required
                            >
                        @endif
                        @error($field['name'])
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </label>
                @endforeach

                <button class="button button-primary button-full" type="submit">
                    {{ $editing ? 'Save changes' : 'Add '.$definition['singular'] }}
                </button>
            </form>

            @if (
                ($resource === 'rooms' && empty($formOptions['dorm_building_id'])) ||
                ($resource === 'bed-slots' && empty($formOptions['dorm_room_id'])) ||
                ($resource === 'contracts' && (empty($formOptions['student_id']) || empty($formOptions['bed_slot_id'])))
            )
                <p class="form-hint">Add the related parent records first to enable this form.</p>
            @endif
        </section>

        <section class="panel table-panel">
            <div class="table-toolbar">
                <form method="GET" action="{{ route($definition['routePrefix'].'.index') }}" class="search-form">
                    <label class="sr-only" for="record-search">Search {{ strtolower($definition['title']) }}</label>
                    <span aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></span>
                    <input id="record-search" type="search" name="search" value="{{ $search }}" placeholder="Search {{ strtolower($definition['title']) }}...">
                    @if ($search !== '')
                        <a class="clear-search" href="{{ route($definition['routePrefix'].'.index') }}">Clear</a>
                    @endif
                </form>
            </div>

            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            @foreach ($definition['columns'] as $column)
                                <th>{{ $column['label'] }}</th>
                            @endforeach
                            <th class="actions-heading">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $entry)
                            <tr>
                                @foreach ($definition['columns'] as $column)
                                    @php($cellValue = data_get($entry, $column['value']))
                                    <td>
                                        @if (in_array($column['value'], ['status', 'lease_status'], true))
                                            <span class="badge badge-{{ strtolower($cellValue) }}">{{ $cellValue }}</span>
                                        @elseif (in_array($column['value'], ['monthly_rate', 'security_deposit'], true))
                                            {{ number_format((float) $cellValue, 2) }}
                                        @else
                                            {{ $cellValue }}
                                        @endif
                                    </td>
                                @endforeach
                                <td>
                                    <div class="row-actions">
                                        <a class="edit-link" href="{{ route($definition['routePrefix'].'.edit', [$definition['routeParameter'] => $entry->id]) }}">Edit</a>
                                        <form method="POST" action="{{ route($definition['routePrefix'].'.destroy', [$definition['routeParameter'] => $entry->id]) }}" onsubmit="return confirm('Delete this record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="delete-link" type="submit">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="table-empty" colspan="{{ count($definition['columns']) + 1 }}">
                                    <span class="empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></span>
                                    <strong>{{ $search !== '' ? 'No matching records' : 'Nothing here yet' }}</strong>
                                    <span>{{ $search !== '' ? 'Try another search.' : 'Use the form to add your first record.' }}</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($records->hasPages())
                <nav class="pagination" aria-label="Pagination">
                    <span>Showing {{ $records->firstItem() }}–{{ $records->lastItem() }} of {{ $records->total() }}</span>
                    <div>
                        @if ($records->onFirstPage())
                            <span class="page-button is-disabled">Previous</span>
                        @else
                            <a class="page-button" href="{{ $records->previousPageUrl() }}">Previous</a>
                        @endif
                        @if ($records->hasMorePages())
                            <a class="page-button" href="{{ $records->nextPageUrl() }}">Next</a>
                        @else
                            <span class="page-button is-disabled">Next</span>
                        @endif
                    </div>
                </nav>
            @endif
        </section>
    </div>
@endsection
