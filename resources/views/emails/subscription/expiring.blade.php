<x-mail::message>
# Hey {{ $user->name }}, heads up! 👋

Your subscription for **{{ $restaurant->name }}** on {{ config('app.name') }} is expiring on **{{ $expiresAt }}**.

To keep your restaurant visible to customers and continue receiving orders, please renew your subscription before the expiry date.

If you've already arranged payment, you can ignore this email — we'll update your account as soon as it's confirmed.

<x-mail::button :url="route('owner.dashboard')">
Go to Dashboard
</x-mail::button>

Need help? Just reply to this email and we'll assist you.

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
