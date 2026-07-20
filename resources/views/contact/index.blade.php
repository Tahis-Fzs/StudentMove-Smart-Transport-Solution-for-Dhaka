<x-app-layout title="Contact">
    <div class="sm-page">
        <header class="sm-page-head" data-reveal>
            <p class="sm-eyebrow">Support</p>
            <h1 class="sm-page-head__title">Contact us</h1>
            <p class="sm-page-head__lede">Questions about routes, passes, or your account — send a note and we’ll get back to you.</p>
        </header>

        <div class="sm-panel" data-reveal style="max-width:640px;">
            @if(session('success'))
                <div class="sm-flash sm-flash--ok" style="margin:0 0 1.25rem;">{{ session('success') }}</div>
            @endif

            <form action="{{ route('contact.store') }}" method="POST">
                @csrf
                <div class="sm-field">
                    <label for="name">Name</label>
                    <input class="form-input" type="text" name="name" id="name" required value="{{ old('name') }}">
                </div>
                <div class="sm-field">
                    <label for="email">Email</label>
                    <input class="form-input" type="email" name="email" id="email" required value="{{ old('email') }}">
                </div>
                <div class="sm-field">
                    <label for="subject">Subject</label>
                    <input class="form-input" type="text" name="subject" id="subject" required value="{{ old('subject') }}">
                </div>
                <div class="sm-field">
                    <label for="message">Message</label>
                    <textarea class="form-input" name="message" id="message" rows="5" required>{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="sm-btn sm-btn--primary">Send message</button>
            </form>
        </div>
    </div>
</x-app-layout>
