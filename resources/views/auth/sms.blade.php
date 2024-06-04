<x-guest-layout>
    <form method="POST" action="{{ route('sms-check') }}">
        @csrf

        <div class="mt-4">
            Silent Authentication is not available in your location.
            Please enter the 4 digit code sent via. SMS to your device.
        </div>

        <div class="mt-4">
            <x-input-label for="code" :value="__('Code')" />

            <x-text-input id="code" class="block mt-1 w-full"
                          type="text"
                          name="code" />

        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-4">
                {{ __('Verify by SMS') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
