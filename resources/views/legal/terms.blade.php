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
        <h1 class="text-3xl md:text-4xl font-black outfit text-gray-900">Terms of Service</h1>
        <p class="mt-2 text-sm font-medium text-gray-500">Last updated: {{ now()->format('F d, Y') }}</p>

        <div class="mt-8 space-y-6 text-gray-700 leading-relaxed">
            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">1. Acceptance of Terms</h2>
                <p>By creating an account, browsing restaurants, placing orders, or using any part of this platform, you agree to these Terms of Service and our Privacy Policy.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">2. Eligibility and Accounts</h2>
                <p>You must provide accurate account information and keep your login credentials secure. You are responsible for activity under your account.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">3. Orders and Availability</h2>
                <p>All orders are subject to restaurant availability, preparation time, and operational constraints. Menus, prices, and item availability may change at any time.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">4. Pricing and Payments</h2>
                <p>Displayed prices may include or exclude delivery, service, or tax amounts depending on your location and checkout details. You authorize us to charge the selected payment method for accepted orders.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">5. Cancellations and Refunds</h2>
                <p>Orders may not be cancellable after restaurant acceptance or preparation has started. Refund eligibility is determined case by case, based on issues such as missing items, failed delivery, or order quality concerns.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">6. Promotions and Credits</h2>
                <p>Promo codes, vouchers, and credits may have eligibility limits, expiration dates, and usage restrictions. Abuse or manipulation of promotional systems may lead to cancellation of benefits or account action.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">7. Acceptable Use</h2>
                <p>You agree not to misuse the platform, interfere with services, commit fraud, submit abusive content, or violate laws. We may suspend or terminate accounts for harmful or unlawful behavior.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">8. Third-Party Restaurants and Delivery</h2>
                <p>Restaurants are responsible for food preparation, ingredients, and quality. Delivery timing and fulfillment may be affected by weather, traffic, or other operational factors outside our control.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">9. Limitation of Liability</h2>
                <p>To the fullest extent allowed by law, the platform is provided as-is, and we are not liable for indirect, incidental, or consequential damages arising from use of the service.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">10. Changes to Terms</h2>
                <p>We may update these terms from time to time. Continued use after updates means you accept the revised version.</p>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-2">11. Contact</h2>
                <p>For questions about these terms, contact us through the Help Center page.</p>
            </section>
        </div>
    </div>
</div>
@endsection

