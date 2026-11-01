<div>
    {{-- Page Head --}}
    <div class="ah-page-head">
        <div>
            <h1 class="ah-page-title">Gold &amp; Silver Rates</h1>
            <p class="ah-page-sub">Type the day's rates here. They show on the public सुनचाँदी page.</p>
        </div>
        <div class="ah-page-actions">
            <a href="{{ route('frontend.tools.gold') }}" target="_blank" rel="noopener" class="uk-button uk-button-default">View public page</a>
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
            <h2 class="ah-card-title">Rates for one day</h2>
        </div>

        <div class="ah-card-body">
            <div class="ah-form-grid">
                <div class="ah-field ah-field--full">
                    <label for="rate_date" class="uk-form-label">Date <span class="ah-required">*</span></label>
                    <input id="rate_date" type="date" wire:model.live="rate_date" max="{{ today()->toDateString() }}" class="uk-input {{ $errors->has('rate_date') ? 'uk-form-danger' : '' }}">
                    @error('rate_date') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="fine_gold" class="uk-form-label">Fine gold (छापावाल), Rs per tola <span class="ah-required">*</span></label>
                    <input id="fine_gold" type="number" min="1" step="1" inputmode="numeric" wire:model="fine_gold" class="uk-input {{ $errors->has('fine_gold') ? 'uk-form-danger' : '' }}">
                    @error('fine_gold') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="tejabi_gold" class="uk-form-label">Tejabi gold (तेजाबी), Rs per tola <span class="ah-required">*</span></label>
                    <input id="tejabi_gold" type="number" min="1" step="1" inputmode="numeric" wire:model="tejabi_gold" class="uk-input {{ $errors->has('tejabi_gold') ? 'uk-form-danger' : '' }}">
                    @error('tejabi_gold') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>

                <div class="ah-field">
                    <label for="silver" class="uk-form-label">Silver (चाँदी), Rs per tola <span class="ah-required">*</span></label>
                    <input id="silver" type="number" min="1" step="1" inputmode="numeric" wire:model="silver" class="uk-input {{ $errors->has('silver') ? 'uk-form-danger' : '' }}">
                    @error('silver') <div class="ah-field-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="ah-field-hint">The last saved rates are filled in to save typing. The page works out the price per 10 grams from these.</div>
        </div>

        <div class="ah-card-foot ah-form-actions">
            <button type="submit" class="uk-button uk-button-primary" wire:loading.attr="disabled" wire:target="save">Save rates</button>
        </div>
    </form>

    <div class="ah-card">
        @if ($rates->total() === 0)
            {{-- Empty State --}}
            <div class="ah-empty ah-empty--section">
                <span class="ah-empty-icon" uk-icon="icon: star; ratio: 2" wire:ignore></span>
                <strong class="ah-empty-title">No rates yet</strong>
                <p>Saved rates will be listed here.</p>
            </div>
        @else
            <div class="ah-table-wrap" wire:loading.class="ah-is-loading" wire:target="delete,gotoPage,previousPage,nextPage">
                <table class="uk-table uk-table-divider uk-table-hover uk-table-middle ah-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Fine gold</th>
                            <th>Tejabi gold</th>
                            <th>Silver</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rates as $rate)
                            <tr wire:key="rate-{{ $rate->id }}">
                                <td class="ah-col-date">{{ $rate->rate_date->format('M j, Y') }}</td>
                                <td>{{ number_format($rate->fine_gold) }}</td>
                                <td>{{ number_format($rate->tejabi_gold) }}</td>
                                <td>{{ number_format($rate->silver) }}</td>
                                <td>
                                    <div class="ah-row-actions">
                                        <button type="button" wire:click="edit({{ $rate->id }})">Edit</button>
                                        <button type="button" class="ah-action-danger" wire:click="delete({{ $rate->id }})" wire:confirm="Delete the rates for this day?">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($rates->hasPages())
                <div class="ah-card-foot">
                    {{ $rates->links('partials.admin-pagination') }}
                </div>
            @endif
        @endif
    </div>
</div>
