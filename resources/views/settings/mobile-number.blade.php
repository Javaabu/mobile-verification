@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Mobile number')" data-mobile-page="settings">
        <div class="flex flex-col gap-4">
            @include('mobile-verification::partials.status')

            <p class="text-sm">{{ __('Your mobile number is used to log in and for SMS notifications.') }}</p>

            @if($current_number)
                <p data-current-number>
                    {!! __('Your mobile number is :number.', ['number' => '<bdi dir="ltr">' . e($current_number['formatted_number']) . '</bdi>']) !!}
                </p>
            @else
                <x-forms::alert type="warning" data-no-number>
                    {{ __('You have not verified a mobile number yet.') }}
                </x-forms::alert>
            @endif

            <x-forms::form :action="$urls['mobile-number.store']" class="flex flex-col gap-4" data-mobile-number-form>
                @include('mobile-verification::partials.number-input', ['label' => $current_number ? __('New mobile number') : __('Mobile number')])

                <x-forms::submit>{{ __('Send code') }}</x-forms::submit>
            </x-forms::form>
        </div>
    </x-forms::card>
@endsection
