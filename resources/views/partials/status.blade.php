@if($status ?? session('status'))
    <x-forms::alert type="success" data-mobile-status>
        {{ $status ?? session('status') }}
    </x-forms::alert>
@endif
