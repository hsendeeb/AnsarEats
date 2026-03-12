<x-mail::message>
# Congratulations, {{ $user->name }}!

Your restaurant, **{{ $restaurant->name }}**, has been successfully created and is now part of the {{ config('app.name') }} family.

You can now start adding menu items and managing orders directly from your owner dashboard.

<x-mail::button :url="route('owner.dashboard')">
Go to Dashboard
</x-mail::button>

Wishing you great success with your business!

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
