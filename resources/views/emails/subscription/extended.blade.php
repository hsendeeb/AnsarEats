<x-mail::message>
# Great news, {{ $user->name }}! 🎉

Your subscription for **{{ $restaurant->name }}** on {{ config('app.name') }} has been extended.

Your subscription is now **valid until {{ $validUntil }}**.

You can continue managing your menu, receiving orders, and growing your business without interruption.

<x-mail::button :url="route('owner.dashboard')">
Go to Dashboard
</x-mail::button>

Thank you for being a valued partner!

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
