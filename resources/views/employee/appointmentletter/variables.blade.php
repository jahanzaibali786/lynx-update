@php
    $appointmentLetterVariables = $variables ?? [];
@endphp

@if (!empty($appointmentLetterVariables))
    <style>
        .appointment-variable-panel {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fbfcff;
        }

        .appointment-variable-group + .appointment-variable-group {
            border-top: 1px solid #e5e7eb;
        }

        .appointment-variable-row + .appointment-variable-row {
            border-top: 1px dashed #e5e7eb;
        }

        .appointment-variable-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .appointment-variable-code {
            display: inline-block;
            margin-top: 4px;
            background: #f4f6fb;
            color: #1f2a44;
            padding: 3px 8px;
            border-radius: 4px;
            text-decoration: underline;
        }

        .appointment-clause-builder {
            border-top: 1px solid #e5e7eb;
            background: #f8faff;
        }

        .appointment-variable-title,
        .appointment-variable-group-title,
        .appointment-variable-label {
            text-decoration: underline;
        }
    </style>

    <div class="form-group mt-3">
        <label class="form-label appointment-variable-title">{{ __('Available Variables') }}</label>
        <div class="appointment-variable-panel">
            @foreach ($appointmentLetterVariables as $groupTitle => $groupVariables)
                <div class="appointment-variable-group p-3">
                    <div class="fw-bold mb-2 appointment-variable-group-title">{{ __($groupTitle) }}</div>

                    <div class="row">
                        @foreach ($groupVariables as $label => $placeholder)
                            <div class="col-md-6 py-2">
                                <div class="appointment-variable-row d-flex align-items-center justify-content-between gap-3 p-2 border rounded bg-white">
                                    <div>
                                        <div class="fw-semibold appointment-variable-label">{{ __($label) }}</div>
                                        <code class="appointment-variable-code">{{ $placeholder }}</code>
                                    </div>
                                    <div class="appointment-variable-actions">
                                        <button type="button" class="btn btn-sm btn-outline-secondary appointment-variable-copy" data-placeholder="{{ $placeholder }}">
                                            {{ __('Copy') }}
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary appointment-variable-insert" data-placeholder="{{ $placeholder }}">
                                            {{ __('Insert') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="appointment-clause-builder p-3">
                <div class="fw-bold mb-2">{{ __('Clause Builder') }}</div>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label mb-1">{{ __('Number') }}</label>
                        <select class="form-control appointment-clause-number-select">
                            @for ($number = 1; $number <= 20; $number++)
                                <option value="{{ $number }}">[[NO:{{ $number }}]]</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1">{{ __('Alphabet') }}</label>
                        <select class="form-control appointment-clause-alpha-select">
                            <option value="">{{ __('None') }}</option>
                            @foreach (range('a', 'f') as $letter)
                                <option value="{{ $letter }}">[[ALP:{{ $letter }}]]</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-1">{{ __('Actions') }}</label>
                        <div class="appointment-variable-actions">
                            <button type="button" class="btn btn-sm btn-outline-secondary appointment-clause-builder-copy">
                                {{ __('Copy') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary appointment-clause-builder-insert">
                                {{ __('Insert') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
