{{-- Star Rating Display --}}
{{-- Usage: @include('layouts.partials.star-rating', ['rating' => $avgRating, 'count' => $totalRatings, 'size' => 'sm']) --}}
@php
    $rating = $rating ?? 0;
    $count = $count ?? 0;
    $size = $size ?? 'sm';
    $showText = $showText ?? true;
    $sizeClass = $size === 'lg' ? 'w-5 h-5' : ($size === 'md' ? 'w-4 h-4' : 'w-3.5 h-3.5');
    $textClass = $size === 'lg' ? 'text-sm' : ($size === 'md' ? 'text-xs' : 'text-[10px]');
@endphp

<div class="flex items-center gap-1">
    @for($i = 1; $i <= 5; $i++)
        @if($i <= floor($rating))
            {{-- Full star --}}
            <svg class="{{ $sizeClass }} text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
        @elseif($i - $rating < 1 && $i - $rating > 0)
            {{-- Half star --}}
            <div class="relative {{ $sizeClass }}">
                <svg class="{{ $sizeClass }} text-gray-200 absolute" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                <div class="overflow-hidden absolute" style="width: {{ ($rating - floor($rating)) * 100 }}%">
                    <svg class="{{ $sizeClass }} text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
            </div>
        @else
            {{-- Empty star --}}
            <svg class="{{ $sizeClass }} text-gray-200" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
        @endif
    @endfor
    @if($showText)
        @if($count > 0)
            <span class="{{ $textClass }} font-bold text-gray-500 ml-1">{{ number_format($rating, 1) }}</span>
            <span class="{{ $textClass }} text-gray-400">({{ $count }})</span>
        @else
            <span class="{{ $textClass }} text-gray-400 ml-1">No ratings</span>
        @endif
    @endif
</div>
