<x-mail::message>
# Welcome to {{ config('app.name') }}, {{ $user->name }}!

We're thrilled to have you join our community. Whether you're here to find the best local eats or start your own culinary journey, we've got you covered.

<x-mail::button :url="config('app.url')">
Explore Meals
</x-mail::button>

Thanks,<br>
The {{ config('app.name') }} Team
</x-mail::message>
