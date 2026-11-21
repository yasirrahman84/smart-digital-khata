<x-app-layout>
    <x-slot name="title">Settings</x-slot>
    <x-slot name="subtitle">Shop profile and reminder preferences</x-slot>

    <div class="max-w-3xl space-y-5">
        <form method="POST" action="{{ route('settings.update') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <h2 class="font-semibold text-stone-900">Shop profile</h2>
            <p class="mt-1 text-sm text-stone-500">Shown on receipts, statements and reminder messages.</p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="store_name" value="Shop name" />
                    <x-text-input id="store_name" name="store_name" type="text" class="mt-1" :value="old('store_name', $user->store_name)" placeholder="e.g. Rahman General Store" />
                    <x-input-error :messages="$errors->get('store_name')" />
                </div>

                <div>
                    <x-input-label for="store_phone" value="Shop phone" />
                    <x-text-input id="store_phone" name="store_phone" type="tel" class="mt-1" :value="old('store_phone', $user->store_phone)" placeholder="0345-1234567" />
                    <x-input-error :messages="$errors->get('store_phone')" />
                </div>

                <div>
                    <x-input-label for="store_address" value="Shop address" />
                    <x-text-input id="store_address" name="store_address" type="text" class="mt-1" :value="old('store_address', $user->store_address)" placeholder="Main Bazaar, Batkhela" />
                    <x-input-error :messages="$errors->get('store_address')" />
                </div>
            </div>

            <div class="mt-6 border-t border-stone-100 pt-5">
                <h2 class="font-semibold text-stone-900">Payment reminders</h2>
                <p class="mt-1 text-sm text-stone-500">
                    Customers who owe money and have not been reminded recently are included in the daily run.
                </p>

                <div class="mt-4 grid gap-5 sm:grid-cols-2">
                    <div>
                        <x-input-label for="reminder_after_days" value="Remind again after (days)" />
                        <x-text-input id="reminder_after_days" name="reminder_after_days" type="number" min="1" max="90" class="mt-1" :value="old('reminder_after_days', $user->reminder_after_days)" required />
                        <x-input-error :messages="$errors->get('reminder_after_days')" />
                    </div>

                    <div class="flex items-end">
                        <label class="flex items-center gap-2 pb-3 text-sm text-stone-700">
                            <input type="hidden" name="sms_enabled" value="0">
                            <input type="checkbox" name="sms_enabled" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" @checked(old('sms_enabled', $user->sms_enabled))>
                            Enable SMS reminders
                        </label>
                    </div>
                </div>

                <div class="mt-4 flex items-start gap-3 rounded-xl bg-stone-50 p-4 text-xs text-stone-600">
                    <x-heroicon-o-information-circle class="mt-0.5 h-4 w-4 shrink-0" />
                    <p>
                        SMS driver: <strong>{{ config('services.sms.driver', 'log') }}</strong>.
                        @if (config('services.sms.driver', 'log') === 'log')
                            Messages are logged to the database instead of being sent, which is safe for demos.
                            Set <code>SMS_DRIVER</code> in <code>.env</code> to use Twilio or an HTTP gateway.
                        @endif
                        The scheduled command <code>khata:send-reminders</code> runs daily at 10:00 when the scheduler is active.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between border-t border-stone-100 pt-5">
                <a href="{{ route('profile.edit') }}" class="text-sm font-semibold text-stone-600 hover:text-stone-900">Account details</a>
                <x-primary-button>
                    <x-heroicon-o-check class="h-5 w-5" />
                    Save settings
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
