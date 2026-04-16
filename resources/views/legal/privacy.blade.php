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
        <h1 class="text-3xl md:text-4xl font-black outfit text-gray-900">Privacy Policy</h1>
        <p class="mt-2 text-sm font-medium text-gray-500">Last updated: {{ now()->format('F d, Y') }}</p>

        <div class="mt-8 space-y-6 text-gray-700 leading-relaxed">
            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">1. Information We Collect</h2>
                <p>We collect information you provide directly (name, email, phone, address, payment details), order details, device and usage data, and support communications.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">2. How We Use Information</h2>
                <p>We use your data to process orders, provide customer support, improve performance, prevent fraud, send service messages, and deliver relevant offers where permitted.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">3. Sharing of Information</h2>
                <p>We share necessary data with restaurants, delivery partners, payment processors, analytics providers, and legal authorities when required by law or to protect users and services.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">4. Data Retention</h2>
                <p>We retain personal data as long as needed for service delivery, legal obligations, dispute resolution, and fraud prevention.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">5. Security</h2>
                <p>We use reasonable administrative, technical, and organizational safeguards to protect personal information. No method of transmission or storage is fully secure.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">6. Your Rights and Choices</h2>
                <p>Depending on your location, you may have rights to access, correct, delete, or limit processing of your personal data, and to manage marketing preferences.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">7. Cookies and Tracking</h2>
                <p>We use cookies and similar technologies for authentication, preferences, analytics, and feature performance. See our Cookie Policy for details.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">8. Children’s Privacy</h2>
                <p>Our services are not intended for children under the age required by local law. We do not knowingly collect personal data from children without proper consent.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">9. Policy Updates</h2>
                <p>We may revise this Privacy Policy from time to time. We will post updates on this page with a revised effective date.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">10. Contact</h2>
                <p>For privacy questions or requests, contact us through the Help Center page.</p>
            </section>
        </div>
    </div>
</div>
@endsection

