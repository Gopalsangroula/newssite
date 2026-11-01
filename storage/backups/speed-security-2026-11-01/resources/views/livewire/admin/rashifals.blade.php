<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Rashifal</h1>
            <p class="ah-page-sub">Write one day's rashifal here. A future date stays hidden until that day comes.</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('frontend.tools.rashifal') }}" target="_blank" rel="noopener" class="uk-button uk-button-default">View public page</a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('message'))
        <div class="uk-alert-success ah-flash" uk-alert role="alert">
            <a class="uk-alert-close" uk-close aria-label="Close alert"></a>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <form wire:submit.prevent="save" class="ah-card ah-form-card" novalidate>
        <div class="ah-card-head">
            <h2 class="ah-card-title">Rashifal for one day</h2>
        </div>

        <div class="ah-card-body">
            <div class="ah-form-grid">
                <div class="ah-field">
                    <label for="rashifal_date" class="uk-form-label">Date <span class="ah-required">*</span></label>
                    <input id="rashifal_date" type="date" wire:model.live="rashifal_date" class="uk-input {{ $errors->has('rashifal_date') ? 'uk-form-danger' : '' }}">
                    @error('rashifal_date') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="author" class="uk-form-label">Written by</label>
                    <input id="author" type="text" wire:model="author" maxlength="120" class="uk-input" placeholder="Optional, shown on the page">
                </div>

                @foreach (\App\Models\Rashifal::SIGNS as $key => $sign)
                    <div class="ah-field" wire:key="sign-{{ $key }}">
                        <label for="reading-{{ $key }}" class="uk-form-label">{{ $sign['symbol'] }}&#xFE0E; {{ $sign['name'] }} <span class="ah-required">*</span></label>
                        <textarea id="reading-{{ $key }}" wire:model="readings.{{ $key }}" rows="3" maxlength="800" class="uk-textarea {{ $errors->has('readings.'.$key) ? 'uk-form-danger' : '' }}"></textarea>
                        @error('readings.'.$key) <div class="ah-field-error">{{ $message }}</div> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="ah-card-foot ah-form-actions">
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" wire:target="save">Save rashifal</button>
        </div>
    </form>

    <div class="ah-card">
        @if ($days->total() === 0)
            {{-- Empty State --}}
            <div class="ah-empty ah-empty--section">
                <span class="ah-empty-icon" uk-icon="icon: star; ratio: 2" wire:ignore></span>
                <strong class="ah-empty-title">No rashifal yet</strong>
                <p>Saved days will be listed here.</p>
            </div>
        @else
            <div class="ah-table-wrap" wire:loading.class="ah-is-loading" wire:target="delete,gotoPage,previousPage,nextPage">
                <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Written by</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($days as $rashifal)
                            <tr wire:key="rashifal-{{ $rashifal->id }}">
                                <td class="ah-col-date">{{ $rashifal->rashifal_date->format('M j, Y') }}</td>
                                <td>{{ $rashifal->author ?: '—' }}</td>
                                <td>
                                    <div class="ah-row-actions">
                                        <button type="button" wire:click="edit({{ $rashifal->id }})">Edit</button>
                                        <button type="button" class="ah-action-danger" wire:click="delete({{ $rashifal->id }})" wire:confirm="Delete the rashifal for this day?">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($days->hasPages())
                <div class="ah-card-foot">
                    {{ $days->links('partials.admin-pagination') }}
                </div>
            @endif
        @endif
    </div>
</div>
