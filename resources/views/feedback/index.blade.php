<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h1 class="text-3xl font-bold mb-6">Feedback</h1>
                    
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Submit Feedback</h2>
                        <form action="{{ route('feedback.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="subject" class="block text-sm font-medium mb-2">Subject</label>
                                <input type="text" name="subject" id="subject" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="message" class="block text-sm font-medium mb-2">Message</label>
                                <textarea name="message" id="message" rows="5" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                            <div>
                                <label for="rating" class="block text-sm font-medium mb-2">Rating (1-5)</label>
                                <input type="number" name="rating" id="rating" min="1" max="5" value="5"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                Submit Feedback
                            </button>
                        </form>
                    </div>

                    @if($feedbacks->count() > 0)
                        <div class="mt-8">
                            <h2 class="text-xl font-semibold mb-4">Your Feedback History</h2>
                            <div class="space-y-4">
                                @foreach($feedbacks as $feedback)
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="font-semibold">{{ $feedback->subject }}</h3>
                                            <span class="text-sm text-gray-500">{{ $feedback->created_at->format('d M, Y') }}</span>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-400 mb-2">{{ $feedback->message }}</p>
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm">Rating:</span>
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="text-yellow-400">{{ $i <= $feedback->rating ? '★' : '☆' }}</span>
                                            @endfor
                                        </div>
                                        @if($feedback->admin_response)
                                            <div class="mt-3 p-3 bg-gray-100 dark:bg-gray-700 rounded">
                                                <strong>Admin Response:</strong> {{ $feedback->admin_response }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

