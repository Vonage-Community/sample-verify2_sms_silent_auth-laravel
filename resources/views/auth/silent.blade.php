<x-guest-layout>
    <form class="w-max" method="POST" action="{{ route('silent-start') }}">
        @csrf
        <!-- Phone Number -->
        <div class="hidden">
            <x-text-input id="phone_number" class="" type="text" name="phone_number" value="{{ session()->get('phone_number') }}" />
        </div>

        <!-- Email -->
        <div class="hidden">
            <x-text-input id="email" class="" type="text" name="email" value="{{ session()->get('email') }}"/>
        </div>

        <div class="hidden">
            <x-text-input id="redirect_url" name="redirect_url" value="" />
        </div>

        <x-primary-button class="w-full">
            {{ __('Start two-factor authentication') }}
        </x-primary-button>
    </form>
</x-guest-layout>

<script>
    const baseUrl = window.location.origin;
    document.getElementById('redirect_url').value = baseUrl + '/silent-callback';
</script>
