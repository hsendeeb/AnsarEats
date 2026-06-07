@extends('layouts.app')

@section('skeleton')
<div class="bg-gray-50 py-12 px-4">
    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-3xl shadow-sm p-6 md:p-10 space-y-6">
        <div class="w-48 h-10 bg-gray-200 dark:bg-gray-700 rounded-xl animate-pulse"></div>
        <div class="w-32 h-4 bg-gray-100 dark:bg-gray-700 rounded animate-pulse"></div>
        <div class="space-y-4 pt-8">
            @for($i=0; $i<6; $i++)
            <div class="space-y-3">
                <div class="w-40 h-6 bg-gray-100 dark:bg-gray-700 rounded animate-pulse"></div>
                <div class="flex gap-4">
                    <div class="w-full h-20 bg-gray-50 dark:bg-gray-900/50 rounded-2xl animate-pulse"></div>
                </div>
            </div>
            @endfor
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="bg-gray-50 py-12 px-4">
    <div class="max-w-4xl mx-auto bg-white border border-gray-100 rounded-3xl shadow-sm p-6 md:p-10">
        <h1 class="text-3xl md:text-4xl font-black outfit text-gray-900">Help Center</h1>
        <p class="mt-2 text-sm font-medium text-gray-500">Support resources for customers and restaurant partners</p>

        <div class="mt-8 space-y-8 text-gray-700 leading-relaxed">
            <section>
                <h2 class="text-xl font-black text-gray-900 mb-3">Ordering Help</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li>If your order is delayed, check the order status page first for real-time updates.</li>
                    <li>If an item is missing, incorrect, or damaged, report the issue as soon as possible after delivery.</li>
                    <li>If your order cannot be fulfilled, you may be eligible for a refund based on order status and issue type.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-3">Payments and Promotions</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Confirm card and billing details before placing the order.</li>
                    <li>Promo codes may have expiration dates, minimum order values, and restaurant restrictions.</li>
                    <li>If a promo does not apply, review eligibility requirements at checkout.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-3">Account and Security</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Keep your account credentials private and use a strong password.</li>
                    <li>Update your address and phone number to avoid delivery issues.</li>
                    <li>Contact support immediately if you suspect unauthorized account activity.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-3">Restaurant Partner Support</h2>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Use the owner dashboard to manage menu, availability, operating hours, and order status.</li>
                    <li>Ensure menu item availability is accurate to avoid cancellations and customer dissatisfaction.</li>
                    <li>Keep prep-time settings realistic to improve on-time delivery performance.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-xl font-black text-gray-900 mb-3">Contact Support</h2>
                <p>For urgent order issues, contact support from your account order history page.</p>
                <p>For legal, policy, or privacy requests, reference the Terms, Privacy, and Cookie pages linked in the footer.</p>
            </section>
        </div>
    </div>
</div>
@endsection

