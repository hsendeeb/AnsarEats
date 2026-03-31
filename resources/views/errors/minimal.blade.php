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
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        <title>{{ $title }} | {{ $brand }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <script src="https://unpkg.com/@dotlottie/player-component@2.7.12/dist/dotlottie-player.mjs" type="module"></script>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen overflow-x-hidden bg-slate-950 text-slate-100">
        <div class="relative isolate min-h-screen">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.24),_transparent_28%),radial-gradient(circle_at_80%_20%,_rgba(34,211,238,0.18),_transparent_24%),linear-gradient(180deg,_#020617_0%,_#0f172a_48%,_#111827_100%)]"></div>
            <div class="absolute inset-x-0 top-0 -z-10 h-72 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.12),_transparent_60%)]"></div>
            <div class="absolute -left-24 top-24 -z-10 h-72 w-72 rounded-full bg-emerald-400/10 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 -z-10 h-80 w-80 rounded-full bg-cyan-400/10 blur-3xl"></div>

            <main class="mx-auto flex min-h-screen w-full max-w-7xl items-center px-4 py-10 sm:px-6 lg:px-8">
                <div class="grid w-full gap-6 lg:grid-cols-[minmax(0,1.35fr)_360px]">
                    <section class="relative overflow-hidden rounded-[2.5rem] border border-white/10 bg-white/10 p-8 shadow-[0_30px_90px_-30px_rgba(15,23,42,0.85)] backdrop-blur-xl sm:p-10 lg:p-12">
                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-emerald-300/70 to-transparent"></div>
                        <div class="absolute right-0 top-0 h-52 w-52 translate-x-1/3 -translate-y-1/3 rounded-full bg-emerald-300/10 blur-3xl"></div>

                        <div class="relative flex h-full flex-col justify-between gap-10">
                            <div class="space-y-8">
                                <h1 class="outfit text-5xl font-black tracking-tight text-dark sm:text-6xl lg:text-7xl">{{ $title }}</h1>

                                <div class="space-y-4">
                                    <span class="inline-flex items-center rounded-full border border-emerald-300/25 bg-emerald-400/10 px-4 py-2 text-[11px] font-black uppercase tracking-[0.35em] text-emerald-200">
                                        Error {{ $code }}
                                    </span>

                                    <div class="space-y-4">
                                        <p class="outfit text-7xl font-black leading-none tracking-[-0.08em] text-white sm:text-8xl lg:text-[9rem]">{{ $code }}</p>
                                        @hasSection('visual')
                                            <div class="flex justify-center">
                                                @yield('visual')
                                            </div>
                                        @endif
                                        <h1 class="outfit max-w-3xl text-4xl font-black tracking-tight text-dark sm:text-5xl lg:text-6xl">{{ $message }}</h1>
                                        <p class="max-w-2xl text-base font-medium leading-8 text-slate-300 sm:text-lg">{{ $description }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <a href="{{ $primaryActionHref }}" class="inline-flex items-center justify-center rounded-full bg-emerald-400 px-6 py-3.5 text-sm font-black text-slate-950 transition hover:-translate-y-0.5 hover:bg-emerald-300">
                                        {{ $primaryActionLabel }}
                                    </a>
                                    <a href="{{ $secondaryActionHref }}" class="inline-flex items-center justify-center rounded-full border border-white/15 bg-white/5 px-6 py-3.5 text-sm font-bold text-white transition hover:border-white/30 hover:bg-white/10">
                                        {{ $secondaryActionLabel }}
                                    </a>
                                </div>
                            </div>

                            <div class="grid gap-4 rounded-[2rem] border border-white/10 bg-slate-950/35 p-5 text-sm text-slate-300 sm:grid-cols-3">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.32em] text-emerald-200">Need help?</p>
                                    <p class="mt-2 font-medium leading-6">Use the quick links to get back to a working part of the app.</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.32em] text-emerald-200">Retry later</p>
                                    <p class="mt-2 font-medium leading-6">Temporary issues usually clear up after a refresh or a short wait.</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.32em] text-emerald-200">Report it</p>
                                    <p class="mt-2 font-medium leading-6">If the problem keeps happening, the team should take a closer look.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside class="rounded-[2.5rem] border border-white/10 bg-slate-900/70 p-6 shadow-[0_30px_90px_-35px_rgba(15,23,42,0.95)] backdrop-blur-xl sm:p-8">
                        <div class="flex h-full flex-col gap-8">
                            <div class="rounded-[2rem] border border-white/10 bg-white/5 p-5">
                                <p class="text-[11px] font-black uppercase tracking-[0.32em] text-emerald-200">What you can try</p>
                                <div class="mt-4 space-y-3 text-sm font-medium leading-7 text-slate-300">
                                    @hasSection('tips')
                                        @yield('tips')
                                    @else
                                        <p>Check the URL for typos or outdated links.</p>
                                        <p>Head back to the homepage and continue browsing from there.</p>
                                        <p>Refresh the page if this looks like a temporary interruption.</p>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-[2rem] border border-emerald-300/15 bg-emerald-400/10 p-5">
                                <p class="text-[11px] font-black uppercase tracking-[0.32em] text-emerald-200">Quick links</p>
                                <div class="mt-4 flex flex-wrap gap-3">
                                    @foreach($quickLinks as $link)
                                        <a href="{{ $link['href'] }}" class="inline-flex items-center rounded-full border border-white/70 bg-white px-4 py-2 text-sm font-bold text-slate-900 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-300 hover:text-slate-950">
                                            {{ $link['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-auto rounded-[2rem] border border-white/10 bg-white/5 p-5">
                                <p class="text-[11px] font-black uppercase tracking-[0.32em] text-slate-400">Backtrack</p>
                                <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('{{ url('/') }}')" class="mt-4 inline-flex w-full items-center justify-center rounded-full border border-white/15 px-5 py-3 text-sm font-bold text-white transition hover:border-white/30 hover:bg-white/10">
                                    Go to the previous page
                                </button>
                            </div>
                        </div>
                    </aside>
                </div>
            </main>
        </div>
    </body>
</html>
