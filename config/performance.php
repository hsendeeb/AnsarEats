<?php

return [
    'cache_ttl' => [
        'home' => (int) env('PERF_CACHE_HOME_TTL', 300),
        'browse' => (int) env('PERF_CACHE_BROWSE_TTL', 300),
        'search' => (int) env('PERF_CACHE_SEARCH_TTL', 120),
        'restaurants' => (int) env('PERF_CACHE_RESTAURANTS_TTL', 300),
        'restaurant_show' => (int) env('PERF_CACHE_RESTAURANT_SHOW_TTL', 300),
        'owner_orders' => (int) env('PERF_CACHE_OWNER_ORDERS_TTL', 30),
        'profile_orders' => (int) env('PERF_CACHE_PROFILE_ORDERS_TTL', 30),
    ],
    'polling' => [
        'owner_visible_ms' => (int) env('OWNER_ORDERS_POLL_VISIBLE_MS', 15000),
        'owner_hidden_ms' => (int) env('OWNER_ORDERS_POLL_HIDDEN_MS', 45000),
        'owner_retry_ms' => (int) env('OWNER_ORDERS_POLL_RETRY_MS', 25000),
        'owner_focus_ms' => (int) env('OWNER_ORDERS_POLL_FOCUS_MS', 2500),
        'profile_visible_ms' => (int) env('PROFILE_ORDERS_POLL_VISIBLE_MS', 12000),
        'profile_hidden_ms' => (int) env('PROFILE_ORDERS_POLL_HIDDEN_MS', 45000),
        'profile_retry_ms' => (int) env('PROFILE_ORDERS_POLL_RETRY_MS', 25000),
        'profile_focus_ms' => (int) env('PROFILE_ORDERS_POLL_FOCUS_MS', 2500),
    ],
];
