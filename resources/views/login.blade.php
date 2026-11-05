@extends($layout)

@section($layout_section)
    <x-forms::card :title="__('Log in with your mobile number')" data-mobile-page="login">
        <div class="flex flex-col gap-4">
            @include('mobile-verification::partials.status')

            <x-forms::form :action="$urls['login.mobile.store']" class="flex flex-col gap-4" data-mobile-number-form>
                @include('mobile-verification::partials.number-input')

                <x-forms::checkbox name="remember" :label="__('Remember me')" value="1" :model="false" />

                <x-forms::submit class="w-full">{{ __('Send code') }}</x-forms::submit>
            </x-forms::form>

            <x-forms::link-button :url="$urls['login']" color="secondary" class="w-full" data-password-login>
                {{ __('Log in with a password instead') }}
            </x-forms::link-button>

            @if($can_register)
                <p class="text-center text-sm">
                    {{ __("Don't have an account?") }}
                    <a href="{{ $urls['register.mobile'] }}" class="underline">{{ __('Sign up') }}</a>
                </p>
            @endif
        </div>
    </x-forms::card>
@endsection
