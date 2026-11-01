<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Patro Events</h1>
            <p class="ah-page-sub">Festivals and holidays on the public patro. Correct any date here, or add your own events.</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('frontend.tools.patro') }}" target="_blank" rel="noopener" class="uk-button uk-button-default">View patro</a>
            <button type="button" class="uk-button uk-button-default" wire:click="importCalendar" wire:loading.attr="disabled" wire:target="importCalendar">Import public calendar</button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @foreach (['message' => 'uk-alert-success', 'error' => 'uk-alert-danger'] as $type => $class)
        @if (session($type))
            <div class="{{ $class }} ah-flash" uk-alert role="alert">
                <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
                <p>{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    <form wire:submit.prevent="save" class="ah-card ah-form-card" novalidate>
        <div class="ah-card-head">
            <h2 class="ah-card-title">{{ $editingId ? 'Edit event' : 'Add an event' }}</h2>
        </div>

        <div class="ah-card-body">
            <div class="ah-form-grid">
                <div class="ah-field">
                    <label for="year" class="uk-form-label">Year (BS)</label>
                    <select id="year" wire:model.live="year" class="uk-select">
                        @foreach (\App\Support\NepaliDate::years() as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ah-field">
                    <label for="month" class="uk-form-label">Month</label>
                    <select id="month" wire:model.live="month" class="uk-select">
                        @foreach (\App\Support\NepaliDate::monthNames() as $index => $monthName)
                            <option value="{{ $index + 1 }}">{{ $monthName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="ah-field">
                    <label for="day" class="uk-form-label">Day <span class="ah-required">*</span></label>
                    <select id="day" wire:model="day" class="uk-select {{ $errors->has('day') ? 'uk-form-danger' : '' }}">
                        @for ($option = 1; $option <= $length; $option++)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endfor
                    </select>
                    @error('day') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="name" class="uk-form-label">Event name <span class="ah-required">*</span></label>
                    <input id="name" type="text" wire:model="name" maxlength="120" class="uk-input {{ $errors->has('name') ? 'uk-form-danger' : '' }}" placeholder="जस्तै: उधौली पर्व">
                    @error('name') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field ah-field--full">
                    <label><input type="checkbox" class="uk-checkbox" wire:model="is_holiday"> Public holiday (shows in red on the patro)</label>
                </div>
            </div>
        </div>

        <div class="ah-card-foot ah-form-actions">
            @if ($editingId)
                <button type="button" class="uk-button uk-button-default" wire:click="startNew">Cancel</button>
            @endif
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" wire:target="save">{{ $editingId ? 'Update event' : 'Add event' }}</button>
        </div>
    </form>

    <div class="ah-card">
        <div class="ah-card-head">
            <h2 class="ah-card-title">{{ \App\Support\NepaliDate::monthNames()[$month - 1] }} {{ $year }}</h2>
        </div>

        @if ($events->isEmpty())
            <div class="ah-empty ah-empty--section">
                <span class="ah-empty-icon" uk-icon="icon: calendar; ratio: 2" wire:ignore></span>
                <strong class="ah-empty-title">No events this month</strong>
                <p>Add one above, or import the public calendar.</p>
            </div>
        @else
            <div class="ah-table-wrap" wire:loading.class="ah-is-loading" wire:target="year,month,delete">
                <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Event</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr wire:key="event-{{ $event->id }}">
                                <td class="ah-col-date">{{ \App\Support\NepaliDate::format($event->event_date) }}</td>
                                <td>{{ $event->name }}</td>
                                <td>
                                    <span @class(['ah-badge', 'ah-badge--danger' => $event->is_holiday])>{{ $event->is_holiday ? 'Holiday' : 'Festival' }}</span>
                                </td>
                                <td>
                                    <div class="ah-row-actions">
                                        <button type="button" wire:click="edit({{ $event->id }})">Edit</button>
                                        <button type="button" class="ah-action-danger" wire:click="delete({{ $event->id }})" wire:confirm="Remove this event from the patro?">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
