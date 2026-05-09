@foreach($restaurants as $restaurant)
    <article class="group bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 relative h-full">
        <a href="{{ route('restaurant.show', $restaurant) }}" class="block h-full">
            <div class="relative h-52 bg-gray-100 overflow-hidden">
                @if($restaurant->logo)
                    <img alt="{{ $restaurant->name }}" src="{{ Storage::url($restaurant->logo) }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                @else
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-emerald-400 via-teal-500 to-cyan-500 text-white text-6xl font-black outfit">
                        {{ substr($restaurant->name, 0, 1) }}
                    </div>
                @endif

                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/75 via-gray-900/20 to-transparent"></div>

                <div class="absolute top-4 right-4 flex flex-col items-end gap-2">
                    <div class="flex items-center gap-1.5 rounded-full px-4 py-1.5 text-xs font-bold shadow-lg backdrop-blur-md {{ $restaurant->isOpenNow() ? 'bg-white/90 text-gray-900' : 'bg-red-50/90 text-red-600' }}">
                        <div class="h-2 w-2 rounded-full {{ $restaurant->isOpenNow() ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                        {{ $restaurant->isOpenNow() ? 'Open Now' : 'Closed' }}
                    </div>

                    @if(auth()->check() && $restaurant->user_id == auth()->id())
                        <div class="bg-amber-500 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg border border-amber-400/50">
                            Own Store
                        </div>
                    @endif
                </div>

                <div class="absolute bottom-4 left-4 flex items-center gap-2">
                    <div class="rounded-full bg-white px-3 py-1 text-xs font-bold text-gray-900 shadow-lg flex items-center gap-1">
                        <svg class="w-3 h-3 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        @if(($restaurant->ratings_count ?? 0) > 0)
                            {{ number_format($restaurant->ratings_avg_rating ?? 0, 1) }}
                        @else
                            New
                        @endif
                    </div>
                    <div class="rounded-full bg-white/20 px-3 py-1 text-xs font-bold text-white shadow-lg backdrop-blur-md">
                        {{ $restaurant->menu_categories_count }} Categories
                    </div>
                </div>
            </div>

            <div class="p-6">
                <h3 class="text-2xl font-black outfit text-gray-900 dark:text-white group-hover:text-emerald-500 transition-colors line-clamp-1">{{ $restaurant->name }}</h3>
                <p class="mt-3 text-sm text-gray-500 font-medium line-clamp-2 min-h-[2.75rem]">
                    {{ $restaurant->description ?? 'Fresh food, signature flavors, and a storefront ready to explore.' }}
                </p>
            </div>
        </a>
    </article>
@endforeach
