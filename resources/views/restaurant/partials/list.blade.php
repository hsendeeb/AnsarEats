@foreach($restaurants as $restaurant)
    <div class="group">
        <a href="{{ route('restaurant.show', $restaurant) }}" class="block h-full relative">
            <div class="bg-white rounded-[2.5rem] overflow-hidden border border-gray-100 shadow-sm hover:shadow-2xl transition-all duration-300 transform group-hover:-translate-y-2 flex flex-col h-full">
                <!-- Image / Logo -->
                <div class="h-48 relative overflow-hidden bg-gray-100">
                    @if($restaurant->logo)
                        <img alt="{{ $restaurant->name }}" src="{{ Storage::url($restaurant->logo) }}" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out"/>
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-emerald-400 via-indigo-500 to-purple-600 flex items-center justify-center text-white font-black text-6xl outfit opacity-80 group-hover:scale-110 transition-transform duration-700 ease-in-out">
                            {{ substr($restaurant->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/60 via-gray-900/20 to-transparent"></div>
                    
                    <div class="absolute top-4 right-4">
                        <div class="bg-white/90 backdrop-blur-md text-gray-900 font-bold px-4 py-1.5 rounded-full text-xs shadow-lg flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full {{ $restaurant->isOpenNow() ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                            {{ $restaurant->isOpenNow() ? 'Open Now' : 'Closed' }}
                        </div>
                    </div>
                </div>

                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="text-xl font-black outfit text-gray-900 group-hover:text-emerald-500 transition-colors line-clamp-1">{{ $restaurant->name }}</h3>
                        <div class="flex items-center gap-1 text-emerald-500 font-bold text-sm bg-emerald-50 px-2.5 py-1 rounded-lg">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            {{ number_format($restaurant->ratings_avg_rating ?? 0, 1) }}
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-500 font-medium mb-4 line-clamp-2">{{ $restaurant->description ?? 'Amazing food, cooked with perfection and delivered straight to you.' }}</p>
                    
                    <div class="mt-auto pt-6 border-t border-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-[10px] font-black text-gray-400 uppercase tracking-widest truncate">
                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ head(explode(',', $restaurant->address)) }}
                        </div>
                        <div class="flex items-center gap-2">
                             <span class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-3 py-1 rounded-full uppercase tracking-tighter">
                                {{ $restaurant->menu_categories_count }} menus
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
@endforeach
