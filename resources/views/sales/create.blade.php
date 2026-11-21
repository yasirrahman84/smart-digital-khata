<x-app-layout>
    <x-slot name="title">New sale</x-slot>
    <x-slot name="subtitle">Pick products, then take payment</x-slot>

    <form
        method="POST"
        action="{{ route('sales.store') }}"
        x-data="pos(
            {{ Js::from($products) }},
            {{ Js::from($customers) }},
            {{ Js::from($initialCart) }},
            {{ Js::from(['discount' => $initialDiscount, 'payment' => $initialPayment, 'customerId' => $initialCustomer, 'notes' => $initialNotes]) }}
        )"
        @submit="submitting = true"
    >
        @csrf

        <div class="grid gap-5 lg:grid-cols-5">
            {{-- Product picker --}}
            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-stone-400">
                            <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                        </span>
                        <x-text-input type="search" class="pl-10" x-model="search" placeholder="Search products by name or SKU…" autofocus />
                    </div>

                    <x-input-error :messages="$errors->get('items')" class="mt-2" />
                    @error('items.*')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-3 grid gap-2 sm:grid-cols-2">
                        <template x-for="product in filtered" :key="product.id">
                            <button
                                type="button"
                                @click="add(product)"
                                :disabled="Number(product.stock_qty) <= 0"
                                class="flex items-center justify-between gap-2 rounded-xl border border-stone-200 p-3 text-left transition hover:border-emerald-400 hover:bg-emerald-50/60 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-stone-900" x-text="product.name"></span>
                                    <span class="block text-xs text-stone-500">
                                        <span x-text="money(product.sale_price)"></span>
                                        <span> / </span>
                                        <span x-text="product.unit"></span>
                                    </span>
                                </span>
                                <span class="shrink-0 text-right">
                                    <span
                                        class="block rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                        :class="Number(product.stock_qty) <= Number(product.low_stock_threshold)
                                            ? 'bg-rose-100 text-rose-700'
                                            : 'bg-stone-100 text-stone-600'"
                                        x-text="Number(product.stock_qty) + ' left'"
                                    ></span>
                                </span>
                            </button>
                        </template>
                    </div>

                    <p x-show="filtered.length === 0" class="py-8 text-center text-sm text-stone-500">
                        No products match your search.
                    </p>
                </div>
            </div>

            {{-- Cart --}}
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-stone-200 bg-white shadow-sm lg:sticky lg:top-20">
                    <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
                        <h2 class="font-semibold text-stone-900">Cart</h2>
                        <button type="button" x-show="cart.length > 0" @click="cart = []" class="text-xs font-semibold text-stone-500 hover:text-rose-600">
                            Clear
                        </button>
                    </div>

                    <div class="max-h-[22rem] overflow-y-auto">
                        <template x-for="(item, index) in cart" :key="item.product_id">
                            <div class="border-b border-stone-100 px-5 py-3">
                                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.product_id">

                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-stone-900" x-text="item.name"></p>
                                        <p class="text-xs text-stone-500">
                                            <span x-text="item.stock + ' in stock'"></span>
                                        </p>
                                    </div>
                                    <button type="button" @click="remove(index)" class="rounded-lg p-1 text-stone-400 hover:bg-rose-50 hover:text-rose-600" aria-label="Remove item">
                                        <x-heroicon-o-x-mark class="h-4 w-4" />
                                    </button>
                                </div>

                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-[11px] font-medium text-stone-500">Qty</label>
                                        <input
                                            type="number"
                                            step="0.001"
                                            min="0.001"
                                            :max="item.stock"
                                            :name="`items[${index}][quantity]`"
                                            x-model.number="item.quantity"
                                            class="block w-full rounded-lg border-stone-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                        >
                                    </div>
                                    <div>
                                        <label class="text-[11px] font-medium text-stone-500">Price (Rs)</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :name="`items[${index}][unit_price]`"
                                            x-model.number="item.unit_price"
                                            class="block w-full rounded-lg border-stone-300 px-2 py-1.5 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                        >
                                    </div>
                                </div>

                                <p class="mt-1.5 text-right text-xs text-stone-500">
                                    Line total: <strong class="text-stone-800" x-text="money((Number(item.quantity) || 0) * (Number(item.unit_price) || 0))"></strong>
                                </p>
                            </div>
                        </template>

                        <div x-show="cart.length === 0" class="px-5 py-10 text-center">
                            <span class="mx-auto grid h-11 w-11 place-items-center rounded-full bg-stone-100 text-stone-400">
                                <x-heroicon-o-shopping-cart class="h-5 w-5" />
                            </span>
                            <p class="mt-3 text-sm font-medium text-stone-700">Cart is empty</p>
                            <p class="mt-1 text-xs text-stone-500">Tap a product to add it.</p>
                        </div>
                    </div>

                    <div class="space-y-3 px-5 py-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-stone-500">Subtotal</span>
                            <strong class="text-stone-900" x-text="money(subtotal)"></strong>
                        </div>

                        <div>
                            <label for="discount" class="text-xs font-medium text-stone-500">Discount (Rs)</label>
                            <input
                                id="discount"
                                type="number"
                                step="0.01"
                                min="0"
                                name="discount"
                                x-model.number="discount"
                                class="mt-1 block w-full rounded-xl border-stone-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                            <x-input-error :messages="$errors->get('discount')" />
                        </div>

                        <div class="flex items-center justify-between border-t border-stone-100 pt-3">
                            <span class="font-semibold text-stone-900">Total</span>
                            <span class="text-xl font-extrabold text-emerald-700" x-text="money(total)"></span>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-stone-500">Payment</p>
                            <div class="mt-1.5 grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="cash" x-model="payment" class="peer sr-only">
                                    <span class="block rounded-xl border border-stone-200 px-3 py-2 text-center text-sm font-semibold text-stone-600 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">
                                        Cash
                                    </span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_method" value="udhaar" x-model="payment" class="peer sr-only">
                                    <span class="block rounded-xl border border-stone-200 px-3 py-2 text-center text-sm font-semibold text-stone-600 transition peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-700">
                                        Udhaar
                                    </span>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('payment_method')" />
                        </div>

                        <div x-show="payment === 'udhaar'" x-cloak>
                            <label for="customer_id" class="text-xs font-medium text-stone-500">Customer (required for udhaar)</label>
                            <select
                                id="customer_id"
                                name="customer_id"
                                x-model="customerId"
                                :required="payment === 'udhaar'"
                                class="mt-1 block w-full rounded-xl border-stone-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                            >
                                <option value="">Choose a customer…</option>
                                <template x-for="customer in customers" :key="customer.id">
                                    <option :value="String(customer.id)" x-text="customer.name"></option>
                                </template>
                            </select>
                            <x-input-error :messages="$errors->get('customer_id')" />
                        </div>

                        <div>
                            <label for="notes" class="text-xs font-medium text-stone-500">Note (optional)</label>
                            <input id="notes" name="notes" type="text" x-model="notes" class="mt-1 block w-full rounded-xl border-stone-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="e.g. Delivered to home">
                        </div>

                        <x-primary-button class="w-full" x-bind:disabled="cart.length === 0 || submitting">
                            <x-heroicon-o-check-circle class="h-5 w-5" />
                            <span x-text="submitting ? 'Saving…' : 'Complete sale'"></span>
                        </x-primary-button>

                        <a href="{{ route('sales.index') }}" class="block text-center text-xs font-semibold text-stone-500 hover:text-stone-700">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            window.pos = function (products, customers, initialCart, initial) {
                return {
                    products,
                    customers,
                    cart: initialCart,
                    search: '',
                    discount: initial.discount || 0,
                    payment: initial.payment || 'cash',
                    customerId: initial.customerId || '',
                    notes: initial.notes || '',
                    submitting: false,

                    get filtered() {
                        const q = this.search.trim().toLowerCase();

                        if (!q) {
                            return this.products;
                        }

                        return this.products.filter((product) =>
                            product.name.toLowerCase().includes(q)
                            || (product.sku || '').toLowerCase().includes(q)
                        );
                    },

                    add(product) {
                        if (Number(product.stock_qty) <= 0) {
                            return;
                        }

                        const existing = this.cart.find((item) => item.product_id === product.id);

                        if (existing) {
                            existing.quantity = Math.min(
                                (Number(existing.quantity) || 0) + 1,
                                Number(product.stock_qty)
                            );

                            return;
                        }

                        this.cart.push({
                            product_id: product.id,
                            name: product.name,
                            unit: product.unit,
                            stock: Number(product.stock_qty),
                            unit_price: Number(product.sale_price),
                            quantity: 1,
                        });
                    },

                    remove(index) {
                        this.cart.splice(index, 1);
                    },

                    get subtotal() {
                        return this.cart.reduce(
                            (sum, item) => sum + (Number(item.quantity) || 0) * (Number(item.unit_price) || 0),
                            0
                        );
                    },

                    get total() {
                        return Math.max(0, this.subtotal - (Number(this.discount) || 0));
                    },

                    money(value) {
                        return 'Rs ' + (Number(value) || 0).toLocaleString('en-PK', {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 2,
                        });
                    },
                };
            };
        </script>
    @endpush
</x-app-layout>
