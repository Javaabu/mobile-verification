@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Your details')" data-mobile-page="register-details">
        <div class="flex flex-col gap-4">
            @include('mobile-verification::partials.status')

            <p class="text-sm">
                {{ __('Your number :number is verified. Tell us a little about yourself.', ['number' => $mobile_number['formatted_number']]) }}
            </p>

            {{ $form }}
        </div>
    </x-forms::card>
@endsection
