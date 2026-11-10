@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Sign up with your mobile number')" data-mobile-page="register">
        <div class="flex flex-col gap-4">
            @include('mobile-verification::partials.status')

            <x-forms::form :action="$urls['register.mobile.store']" class="flex flex-col gap-4" data-mobile-number-form>
                @include('mobile-verification::partials.number-input')

                <x-forms::submit class="w-full">{{ __('Send code') }}</x-forms::submit>
            </x-forms::form>

            <p class="text-center text-sm">
                {{ __('Already have an account?') }}
                <a href="{{ $urls['login.mobile'] }}" class="underline">{{ __('Log in') }}</a>
            </p>
        </div>
    </x-forms::card>
@endsection
