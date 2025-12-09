<x-app-layout>
    <div class="min-h-screen flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8 text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white">
                Welcome to StudentMove
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-300">
                Smart Transport Solution for Dhaka City Students
            </p>
            <div class="mt-8">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                        Go to Dashboard
                    </a>
                @else
                    <div class="flex gap-4 justify-center">
                        <a href="{{ route('login') }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="inline-block bg-gray-200 text-gray-800 px-6 py-3 rounded-lg hover:bg-gray-300 transition">
                            Register
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</x-app-layout>

