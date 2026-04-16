@extends('layouts.app')

@section('skeleton')
<div class="bg-gray-50 py-12 px-4">
    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-3xl shadow-sm p-6 md:p-10 space-y-6">
        <div class="w-48 h-10 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
        <div class="w-32 h-4 bg-gray-100 dark:bg-gray-700 rounded animate-pulse"></div>
        <div class="space-y-4 pt-8">
            @for($i=0; $i<6; $i++)
            <div class="space-y-2">
                <div class="w-40 h-6 bg-gray-100 dark:bg-gray-700 rounded animate-pulse"></div>
                <div class="w-full h-4 bg-gray-50 dark:bg-gray-900/50 rounded animate-pulse"></div>
                <div class="w-full h-4 bg-gray-50 dark:bg-gray-900/50 rounded animate-pulse"></div>
                <div class="w-2/3 h-4 bg-gray-50 dark:bg-gray-900/50 rounded animate-pulse"></div>
            </div>
            @endfor
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="bg-gray-50 py-12 px-4">
    <div class="max-w-4xl mx-auto bg-white border border-gray-100 rounded-3xl shadow-sm p-6 md:p-10">
        <h1 class="text-3xl md:text-4xl font-black outfit text-gray-900">Cookie Policy</h1>
        <p class="mt-2 text-sm font-medium text-gray-500">Last updated: {{ now()->format('F d, Y') }}</p>

        <div class="mt-8 space-y-6 text-gray-700 leading-relaxed">
            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">1. What Are Cookies</h2>
                <p>Cookies are small text files stored on your browser or device to help websites remember preferences, sessions, and usage patterns.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">2. How We Use Cookies</h2>
                <p>We use cookies to keep you signed in, remember preferences, secure sessions, analyze site performance, and support core features such as cart and checkout.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">3. Types of Cookies We Use</h2>
                <p><strong>Essential:</strong> required for core platform functionality and security.</p>
                <p><strong>Functional:</strong> improve convenience by remembering settings and recent actions.</p>
                <p><strong>Analytics:</strong> help us understand usage patterns and improve performance.</p>
                <p><strong>Marketing:</strong> may be used to measure campaign effectiveness where permitted.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">4. Third-Party Cookies</h2>
                <p>Some cookies may be set by third-party services such as analytics, embedded media, or payment-related tools.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">5. Managing Cookies</h2>
                <p>You can control or delete cookies through browser settings. Blocking some cookies may impact features like login persistence, cart behavior, and checkout flow.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">6. Changes to This Policy</h2>
                <p>We may update this Cookie Policy to reflect legal, operational, or product changes. Updates will appear on this page.</p>
            </section>
        </div>
    </div>
</div>
@endsection

