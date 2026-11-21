@props(['customer' => null])

@php
    $isEdit = $customer !== null && $customer->exists;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Customer name" />
        <x-text-input id="name" name="name" type="text" class="mt-1" :value="old('name', $customer?->name)" required autofocus placeholder="e.g. Ali Raza" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="phone" value="Phone number" />
        <x-text-input id="phone" name="phone" type="tel" class="mt-1" :value="old('phone', $customer?->phone)" placeholder="0300-1234567" />
        <p class="mt-1 text-xs text-stone-500">Used for SMS reminders and the public statement link.</p>
        <x-input-error :messages="$errors->get('phone')" />
    </div>

    <div>
        <x-input-label for="opening_balance" value="Opening balance (Rs)" />
        <x-text-input id="opening_balance" name="opening_balance" type="number" step="0.01" min="0" class="mt-1" :value="old('opening_balance', $customer?->opening_balance ?? 0)" />
        <p class="mt-1 text-xs text-stone-500">What they already owe you before using this app.</p>
        <x-input-error :messages="$errors->get('opening_balance')" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="address" value="Address (optional)" />
        <x-text-input id="address" name="address" type="text" class="mt-1" :value="old('address', $customer?->address)" placeholder="Street, mohalla, village" />
        <x-input-error :messages="$errors->get('address')" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes (optional)" />
        <x-textarea id="notes" name="notes" rows="2" class="mt-1" placeholder="Anything you want to remember about this customer">{{ old('notes', $customer?->notes) }}</x-textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>

    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm text-stone-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_active', $customer?->is_active ?? true))>
            Active customer (shown in reminders)
        </label>
        <x-input-error :messages="$errors->get('is_active')" />
    </div>
</div>
