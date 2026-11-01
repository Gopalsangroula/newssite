<?php

use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Appearance')] class extends Component {
    //
}; ?>

<section>
    <x-settings.layout current="appearance">
        {{-- Flux's script is not loaded on admin pages, so this calls the helper @fluxAppearance adds to the head. --}}
        <div
            class="ah-card ah-form-card"
            x-data="{ appearance: window.localStorage.getItem('flux.appearance') || 'system' }"
            x-init="$watch('appearance', (value) => window.Flux.applyAppearance(value))"
        >
            <div class="ah-card-head">
                <div>
                    <h2 class="ah-card-title">{{ __('Appearance') }}</h2>
                    <p class="ah-card-sub">{{ __('Update the appearance settings for your account') }}</p>
                </div>
            </div>

            <div class="ah-card-body">
                <fieldset class="ah-fieldset">
                    <legend class="uk-hidden-visually">{{ __('Appearance') }}</legend>
                    <div class="ah-option-panel ah-option-grid">
                        @foreach (['light' => __('Light'), 'dark' => __('Dark'), 'system' => __('System')] as $value => $label)
                            <label class="ah-option">
                                <input type="radio" name="appearance" value="{{ $value }}" x-model="appearance" class="uk-radio">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </div>
        </div>
    </x-settings.layout>
</section>
