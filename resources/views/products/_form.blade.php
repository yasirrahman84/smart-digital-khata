@props(['product' => null])

@php
    $isEdit = $product !== null && $product->exists;
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Product name" />
        <x-text-input id="name" name="name" type="text" class="mt-1" :value="old('name', $product?->name)" required autofocus placeholder="e.g. Sugar 1kg" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="sku" value="SKU / code (optional)" />
        <x-text-input id="sku" name="sku" type="text" class="mt-1" :value="old('sku', $product?->sku)" placeholder="e.g. SKU-0001" />
        <x-input-error :messages="$errors->get('sku')" />
    </div>

    <div>
        <x-input-label for="unit" value="Unit" />
        <x-text-input id="unit" name="unit" type="text" class="mt-1" list="unit-options" :value="old('unit', $product?->unit ?? 'piece')" required placeholder="piece" />
        <datalist id="unit-options">
            @foreach (['piece', 'kg', 'gram', 'litre', 'ml', 'packet', 'dozen', 'bag', 'bottle', 'box'] as $unit)
                <option value="{{ $unit }}"></option>
            @endforeach
        </datalist>
        <x-input-error :messages="$errors->get('unit')" />
    </div>

    <div>
        <x-input-label for="purchase_price" value="Purchase price (Rs)" />
        <x-text-input id="purchase_price" name="purchase_price" type="number" step="0.01" min="0" class="mt-1" :value="old('purchase_price', $product?->purchase_price)" required />
        <x-input-error :messages="$errors->get('purchase_price')" />
    </div>

    <div>
        <x-input-label for="sale_price" value="Sale price (Rs)" />
        <x-text-input id="sale_price" name="sale_price" type="number" step="0.01" min="0" class="mt-1" :value="old('sale_price', $product?->sale_price)" required />
        <x-input-error :messages="$errors->get('sale_price')" />
    </div>

    @unless ($isEdit)
        <div>
            <x-input-label for="stock_qty" value="Opening stock" />
            <x-text-input id="stock_qty" name="stock_qty" type="number" step="0.001" min="0" class="mt-1" :value="old('stock_qty', $product?->stock_qty ?? 0)" required />
            <x-input-error :messages="$errors->get('stock_qty')" />
        </div>
    @endunless

    <div>
        <x-input-label for="low_stock_threshold" value="Low-stock alert at" />
        <x-text-input id="low_stock_threshold" name="low_stock_threshold" type="number" step="0.001" min="0" class="mt-1" :value="old('low_stock_threshold', $product?->low_stock_threshold ?? 5)" required />
        <p class="mt-1 text-xs text-stone-500">Alert when stock reaches this number.</p>
        <x-input-error :messages="$errors->get('low_stock_threshold')" />
    </div>

    <div class="sm:col-span-2">
        <label class="flex items-center gap-2 text-sm text-stone-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" @checked(old('is_active', $product?->is_active ?? true))>
            Active (available in the POS and stock alerts)
        </label>
        <x-input-error :messages="$errors->get('is_active')" />
    </div>
</div>
