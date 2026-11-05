@extends($layout)

@section($layout_section)
    <x-forms::card :title="$title" data-mobile-page="verify" data-purpose="{{ $purpose }}">
        <div class="flex flex-col gap-4">
            @include('mobile-verification::partials.status')

            <p class="text-sm">
                {!! __('We sent a :length digit code to :number. It expires in :minutes minutes.', [
                    'length' => e($mobile['code_length']),
                    'number' => '<bdi dir="ltr">' . e($mobile_number['formatted_number']) . '</bdi>',
                    'minutes' => e(max(1, (int) ceil($mobile_number['expires_in'] / 60))),
                ]) !!}
            </p>

            <x-forms::form :action="$verify_url" class="flex flex-col gap-4" data-verification-code-form>
                <x-forms::text
                    name="verification_code"
                    :label="__('Code')"
                    :model="false"
                    required
                    autofocus
                    autocomplete="one-time-code"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    :maxlength="$mobile['code_length']"
                    dir="ltr"
                    class="text-center tracking-widest" />

                <x-forms::submit class="w-full">{{ __('Verify') }}</x-forms::submit>
            </x-forms::form>

            <div class="flex flex-wrap items-center justify-between gap-2">
                <x-forms::form :action="$resend_url" data-resend-form>
                    <x-forms::button type="submit" color="secondary">{{ __('Send a new code') }}</x-forms::button>
                </x-forms::form>

                <a href="{{ $change_url }}" class="text-sm underline" data-change-number>{{ __('Change number') }}</a>
            </div>

            @if($mobile_number['resend_in'] > 0)
                <p class="text-sm" data-resend-in>
                    {{ __('You can ask for a new code in :seconds seconds.', ['seconds' => $mobile_number['resend_in']]) }}
                </p>
            @endif
        </div>
    </x-forms::card>
@endsection
