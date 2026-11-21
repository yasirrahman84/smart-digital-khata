<x-app-layout>
    <x-slot name="title">Notifications</x-slot>
    <x-slot name="subtitle">Stock alerts and shop activity</x-slot>

    <div class="flex items-center justify-between">
        <p class="text-sm text-stone-500">
            {{ auth()->user()->unreadNotifications()->count() }} unread
        </p>

        @if (auth()->user()->unreadNotifications()->count() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <x-secondary-button>
                    <x-heroicon-o-check class="h-4 w-4" />
                    Mark all as read
                </x-secondary-button>
            </form>
        @endif
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        @if ($notifications->isEmpty())
            <x-empty-state
                icon="bell"
                title="No notifications yet"
                description="You will be alerted here when a product drops to its low-stock threshold."
            />
        @else
            <ul class="divide-y divide-stone-100">
                @foreach ($notifications as $notification)
                    @php($data = $notification->data)
                    @php($unread = $notification->read_at === null)
                    <li class="{{ $unread ? 'bg-amber-50/60' : 'bg-white' }}">
                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="block">
                            @csrf
                            <button type="submit" class="flex w-full items-start gap-3 px-5 py-4 text-left hover:bg-stone-50">
                                <span class="mt-0.5 grid h-9 w-9 shrink-0 place-items-center rounded-xl {{ $unread ? 'bg-amber-100 text-amber-700' : 'bg-stone-100 text-stone-500' }}">
                                    <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                                </span>

                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-stone-900">{{ $data['product_name'] ?? 'Stock alert' }} is running low</span>
                                        @if ($unread)
                                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                        @endif
                                    </span>
                                    <span class="mt-0.5 block text-sm text-stone-600">
                                        {{ $data['message'] ?? 'Check your stock levels.' }}
                                    </span>
                                    <span class="mt-1 block text-xs text-stone-400">{{ $notification->created_at->diffForHumans() }}</span>
                                </span>

                                <x-heroicon-o-chevron-right class="mt-2 h-4 w-4 shrink-0 text-stone-400" />
                            </button>
                        </form>
                    </li>
                @endforeach
            </ul>

            <div class="border-t border-stone-100 px-5 py-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
