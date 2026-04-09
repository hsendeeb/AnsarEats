@php
    $title = trim($__env->yieldContent('title')) ?: __('Something went wrong');
    $code = trim($__env->yieldContent('code')) ?: 'Error';
    $message = trim($__env->yieldContent('message')) ?: __('Something went wrong');
    $description = trim($__env->yieldContent('description')) ?: __('The page you requested is not available right now. Please try one of the options below.');
    $primaryActionLabel = trim($__env->yieldContent('primary_action_label')) ?: __('Back to home');
    $primaryActionHref = trim($__env->yieldContent('primary_action_href')) ?: url('/');
    $secondaryActionLabel = trim($__env->yieldContent('secondary_action_label')) ?: __('Explore restaurants');
    $secondaryActionHref = trim($__env->yieldContent('secondary_action_href'))
        ?: (\Illuminate\Support\Facades\Route::has('restaurants.index') ? route('restaurants.index') : url('/'));
    $brand = config('app.name', 'Laravel');
    $quickLinks = array_filter([
        [
            'label' => __('Home'),
            'href' => url('/'),
        ],
        \Illuminate\Support\Facades\Route::has('restaurants.index')
            ? ['label' => __('Explore'), 'href' => route('restaurants.index')]
            : null,
        \Illuminate\Support\Facades\Route::has('help.center')
            ? ['label' => __('Help Center'), 'href' => route('help.center')]
            : null,
        \Illuminate\Support\Facades\Route::has('login')
            ? ['label' => __('Log In'), 'href' => route('login')]
            : null,
    ]);

    $theme = match ((string) $code) {
        '401' => [
            'name' => __('Sign In Required'),
            'accent' => '#0f766e',
            'soft' => 'rgba(15, 118, 110, 0.12)',
            'glow' => 'rgba(45, 212, 191, 0.22)',
            'highlight' => '#14b8a6',
        ],
        '402' => [
            'name' => __('Payment Check'),
            'accent' => '#ca8a04',
            'soft' => 'rgba(202, 138, 4, 0.12)',
            'glow' => 'rgba(250, 204, 21, 0.24)',
            'highlight' => '#eab308',
        ],
        '403' => [
            'name' => __('Restricted Area'),
            'accent' => '#2563eb',
            'soft' => 'rgba(37, 99, 235, 0.12)',
            'glow' => 'rgba(96, 165, 250, 0.22)',
            'highlight' => '#3b82f6',
        ],
        '404' => [
            'name' => __('Lost Route'),
            'accent' => '#059669',
            'soft' => 'rgba(5, 150, 105, 0.12)',
            'glow' => 'rgba(16, 185, 129, 0.22)',
            'highlight' => '#10b981',
        ],
        '419' => [
            'name' => __('Session Expired'),
            'accent' => '#ea580c',
            'soft' => 'rgba(234, 88, 12, 0.12)',
            'glow' => 'rgba(251, 146, 60, 0.22)',
            'highlight' => '#f97316',
        ],
        '429' => [
            'name' => __('Cooldown Active'),
            'accent' => '#7c3aed',
            'soft' => 'rgba(124, 58, 237, 0.12)',
            'glow' => 'rgba(167, 139, 250, 0.22)',
            'highlight' => '#8b5cf6',
        ],
        '500' => [
            'name' => __('Server Recovery'),
            'accent' => '#dc2626',
            'soft' => 'rgba(220, 38, 38, 0.12)',
            'glow' => 'rgba(248, 113, 113, 0.22)',
            'highlight' => '#ef4444',
        ],
        '503' => [
            'name' => __('Maintenance Window'),
            'accent' => '#4f46e5',
            'soft' => 'rgba(79, 70, 229, 0.12)',
            'glow' => 'rgba(129, 140, 248, 0.22)',
            'highlight' => '#6366f1',
        ],
        default => [
            'name' => __('Fallback Screen'),
            'accent' => '#0f766e',
            'soft' => 'rgba(15, 118, 110, 0.12)',
            'glow' => 'rgba(45, 212, 191, 0.22)',
            'highlight' => '#14b8a6',
        ],
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        <title>{{ $title }} | {{ $brand }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/ansareats-logo-v2.svg') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-[#f6faf7] text-slate-900">
        <div class="relative isolate min-h-screen overflow-hidden">
            <div class="absolute inset-0 -z-20 bg-[linear-gradient(180deg,#fbfffd_0%,#f3f7f5_55%,#eef4f1_100%)]"></div>
            <div class="absolute inset-x-0 top-0 -z-10 h-[32rem] opacity-90" style="background: radial-gradient(circle at top left, {{ $theme['glow'] }} 0%, transparent 42%), radial-gradient(circle at top right, rgba(15, 23, 42, 0.06) 0%, transparent 35%);"></div>
            <div class="absolute -left-24 top-20 -z-10 h-72 w-72 rounded-full blur-3xl" style="background: {{ $theme['soft'] }};"></div>
            <div class="absolute right-0 top-1/3 -z-10 h-96 w-96 rounded-full blur-3xl" style="background: {{ $theme['glow'] }};"></div>

            <main class="mx-auto flex min-h-screen w-full max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
                <div class="grid w-full gap-6 lg:grid-cols-[minmax(0,1.25fr)_370px]">
                    <section class="relative overflow-hidden rounded-[2.75rem] border border-white/80 bg-white/90 p-8 shadow-[0_35px_90px_-35px_rgba(15,23,42,0.28)] backdrop-blur sm:p-10 lg:p-12">
                        <div class="absolute inset-x-0 top-0 h-1" style="background: linear-gradient(90deg, transparent 0%, {{ $theme['highlight'] }} 50%, transparent 100%);"></div>
                        <div class="absolute -right-16 top-10 h-40 w-40 rounded-full blur-3xl" style="background: {{ $theme['soft'] }};"></div>

                        <div class="relative flex h-full flex-col gap-10">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm font-bold text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:text-slate-950">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-full text-white shadow-sm" style="background: linear-gradient(135deg, {{ $theme['accent'] }} 0%, {{ $theme['highlight'] }} 100%);">AE</span>
                                    <span class="outfit text-lg font-black tracking-tight">{{ $brand }}</span>
                                </a>

                                <div class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-[11px] font-black uppercase tracking-[0.32em]" style="background: {{ $theme['soft'] }}; color: {{ $theme['accent'] }};">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $theme['highlight'] }};"></span>
                                    {{ $theme['name'] }}
                                </div>
                            </div>

                            <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_280px] xl:items-start">
                                <div class="space-y-8">
                                    <div class="space-y-5">
                                        <div class="inline-flex items-center gap-3 rounded-full border border-slate-200/80 bg-slate-50 px-4 py-2">
                                            <span class="outfit text-2xl font-black tracking-[-0.08em]" style="color: {{ $theme['accent'] }};">{{ $code }}</span>
                                            <span class="text-[11px] font-black uppercase tracking-[0.32em] text-slate-500">{{ __('Laravel Fallback') }}</span>
                                        </div>

                                        <div class="space-y-4">
                                            <p class="outfit text-6xl font-black leading-none tracking-[-0.08em] text-slate-900 sm:text-7xl lg:text-[5.5rem]">{{ $code }}</p>
                                            <h1 class="outfit max-w-3xl text-4xl font-black tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">{{ $message }}</h1>
                                            <p class="max-w-2xl text-base font-medium leading-8 text-slate-600 sm:text-lg">{{ $description }}</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-3 sm:flex-row">
                                        <a href="{{ $primaryActionHref }}" class="inline-flex items-center justify-center rounded-full px-6 py-3.5 text-sm font-black text-white transition hover:-translate-y-0.5" style="background: linear-gradient(135deg, {{ $theme['accent'] }} 0%, {{ $theme['highlight'] }} 100%); box-shadow: 0 18px 40px -22px {{ $theme['accent'] }};">
                                            {{ $primaryActionLabel }}
                                        </a>
                                        <a href="{{ $secondaryActionHref }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-6 py-3.5 text-sm font-bold text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-300 hover:text-slate-950">
                                            {{ $secondaryActionLabel }}
                                        </a>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-3">
                                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50/80 p-5">
                                            <p class="text-[11px] font-black uppercase tracking-[0.28em]" style="color: {{ $theme['accent'] }};">{{ __('What Happened') }}</p>
                                            <p class="mt-3 text-sm font-medium leading-6 text-slate-600">{{ __('A safe fallback page was rendered for this response so the app still has a clear next step.') }}</p>
                                        </div>
                                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50/80 p-5">
                                            <p class="text-[11px] font-black uppercase tracking-[0.28em]" style="color: {{ $theme['accent'] }};">{{ __('Best Move') }}</p>
                                            <p class="mt-3 text-sm font-medium leading-6 text-slate-600">{{ __('Use the main action button first, then try the quick links if you want another route through the app.') }}</p>
                                        </div>
                                        <div class="rounded-[1.75rem] border border-slate-200 bg-slate-50/80 p-5">
                                            <p class="text-[11px] font-black uppercase tracking-[0.28em]" style="color: {{ $theme['accent'] }};">{{ __('Still Stuck') }}</p>
                                            <p class="mt-3 text-sm font-medium leading-6 text-slate-600">{{ __('If the same error repeats, it is usually worth checking permissions, session state, or server logs.') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-[2rem] border border-slate-200 bg-slate-50/80 p-5 shadow-inner">
                                    <div class="relative overflow-hidden rounded-[1.75rem] border border-white bg-white p-5 shadow-sm">
                                        <div class="absolute -right-12 -top-12 h-28 w-28 rounded-full blur-3xl" style="background: {{ $theme['glow'] }};"></div>
                                        <p class="text-[11px] font-black uppercase tracking-[0.32em] text-slate-400">{{ __('Status Snapshot') }}</p>

                                        @hasSection('visual')
                                            <div class="mt-3 flex min-h-[210px] items-center justify-center">
                                                @yield('visual')
                                            </div>
                                        @else
                                            <div class="mt-5 flex min-h-[210px] items-center justify-center">
                                                <div class="relative flex h-44 w-44 items-center justify-center rounded-full border-[14px] border-slate-100 bg-white shadow-inner">
                                                    <div class="absolute inset-3 rounded-full" style="background: linear-gradient(135deg, {{ $theme['soft'] }} 0%, rgba(255,255,255,0.9) 100%);"></div>
                                                    <div class="relative text-center">
                                                        <p class="outfit text-6xl font-black tracking-[-0.08em]" style="color: {{ $theme['accent'] }};">{{ $code }}</p>
                                                        <p class="mt-1 text-[11px] font-black uppercase tracking-[0.32em] text-slate-400">{{ $theme['name'] }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="mt-4 rounded-[1.5rem] p-4" style="background: linear-gradient(135deg, {{ $theme['soft'] }} 0%, rgba(255,255,255,0.94) 100%);">
                                            <p class="text-[11px] font-black uppercase tracking-[0.28em]" style="color: {{ $theme['accent'] }};">{{ __('Response Code') }}</p>
                                            <div class="mt-2 flex items-end justify-between gap-4">
                                                <div>
                                                    <p class="outfit text-3xl font-black text-slate-900">{{ $code }}</p>
                                                    <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
                                                </div>
                                                <span class="rounded-full bg-white px-3 py-1 text-[11px] font-black uppercase tracking-[0.26em] text-slate-500 shadow-sm">{{ __('Fallback Ready') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside class="flex flex-col gap-5">
                        <div class="rounded-[2.5rem] border border-white/80 bg-white/90 p-6 shadow-[0_28px_70px_-40px_rgba(15,23,42,0.24)] backdrop-blur sm:p-8">
                            <p class="text-[11px] font-black uppercase tracking-[0.32em] text-slate-400">{{ __('What You Can Try') }}</p>
                            <div class="mt-5 space-y-3 text-sm font-medium leading-7 text-slate-600">
                                @hasSection('tips')
                                    @yield('tips')
                                @else
                                    <p>{{ __('Check the URL for typos or outdated links.') }}</p>
                                    <p>{{ __('Head back to the homepage and continue browsing from there.') }}</p>
                                    <p>{{ __('Refresh the page if this looks like a temporary interruption.') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-[2.5rem] border border-white/80 bg-white/90 p-6 shadow-[0_28px_70px_-40px_rgba(15,23,42,0.24)] backdrop-blur sm:p-8">
                            <p class="text-[11px] font-black uppercase tracking-[0.32em] text-slate-400">{{ __('Quick Links') }}</p>
                            <div class="mt-5 flex flex-wrap gap-3">
                                @foreach($quickLinks as $link)
                                    <a href="{{ $link['href'] }}" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-bold text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white hover:text-slate-950">
                                        {{ $link['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-[2.5rem] border border-white/80 bg-white/90 p-6 shadow-[0_28px_70px_-40px_rgba(15,23,42,0.24)] backdrop-blur sm:p-8">
                            <p class="text-[11px] font-black uppercase tracking-[0.32em] text-slate-400">{{ __('Backtrack') }}</p>
                            <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('{{ url('/') }}')" class="mt-5 inline-flex w-full items-center justify-center rounded-full border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-bold text-slate-700 transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white hover:text-slate-950">
                                {{ __('Go to the previous page') }}
                            </button>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </body>
</html>
