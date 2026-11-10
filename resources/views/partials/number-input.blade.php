{{-- The country code (a select when there's a choice) and the number --}}
@php($country_codes = $mobile['country_codes'])
@php($label = $label ?? __('Mobile number'))

@if(count($country_codes) > 1)
    <div class="grid grid-cols-3 gap-2">
        <x-forms::select
            name="country_code"
            :label="__('Country code')"
            :options="$country_codes"
            :default="$mobile['default_country_code']"
            :model="false"
            required
            dir="ltr" />

        <div class="col-span-2">
            <x-forms::tel
                name="number"
                :label="$label"
                :model="false"
                required
                autofocus
                autocomplete="tel-national"
                inputmode="tel"
                dir="ltr">
                <x-slot:help>{{ __('We will send you a code by SMS.') }}</x-slot:help>
            </x-forms::tel>
        </div>
    </div>
@else
    <input type="hidden" name="country_code" value="{{ array_key_first($country_codes) }}">

    <x-forms::tel
        name="number"
        :label="$label"
        :model="false"
        required
        autofocus
        autocomplete="tel-national"
        inputmode="tel"
        dir="ltr">
        <x-slot:prepend><span dir="ltr">{{ reset($country_codes) }}</span></x-slot:prepend>
        <x-slot:help>{{ __('We will send you a code by SMS.') }}</x-slot:help>
    </x-forms::tel>
@endif
