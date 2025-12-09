<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-3xl font-bold mb-6">Special Offers</h1>
                    
                    @if($offers->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($offers as $offer)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition">
                                    <h2 class="text-xl font-semibold mb-2">{{ $offer->title }}</h2>
                                    @if($offer->description)
                                        <p class="text-gray-600 dark:text-gray-400 mb-4">{{ $offer->description }}</p>
                                    @endif
                                    @if($offer->discount_percentage > 0)
                                        <div class="text-2xl font-bold text-green-600 mb-2">
                                            {{ $offer->discount_percentage }}% OFF
                                        </div>
                                    @endif
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        Valid until: {{ $offer->valid_until->format('d M, Y') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600 dark:text-gray-400">No active offers at the moment. Check back later!</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

