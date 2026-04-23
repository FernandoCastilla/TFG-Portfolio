@props(['links'])

<nav class="flex text-sm text-gray-500 mb-6 font-medium" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-2">
        @foreach($links as $link)
            <li class="inline-flex items-center">
                
                @if(!$loop->first)
                    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                @endif

                @if(isset($link['url']) && !$loop->last)
                    <a href="{{ $link['url'] }}" class="hover:text-teal-700 transition-colors {{ $loop->first ? 'flex items-center gap-1' : 'truncate max-w-[200px]' }}">
                        @if($loop->first)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        @endif
                        {{ $link['label'] }}
                    </a>
                @else
                    <span class="text-gray-800 truncate max-w-[200px]">{{ $link['label'] }}</span>
                @endif
                
            </li>
        @endforeach
    </ol>
</nav>