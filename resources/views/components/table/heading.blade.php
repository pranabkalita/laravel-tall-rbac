@props([
'sortable' => null,
'direction' => null
])

<th {{ $attributes->merge(['class' => 'py-3 px-6 text-left dark:bg-zinc-700 border-b dark:dark:border-zinc-500'])->only('class') }}>
    @unless ($sortable)
    <span class="text-left text-xs leading-4 font-medium uppercase tracking-wider">{{ $slot }}</span>
    @else
    <button
        {{ $attributes->except('class') }} class="flex items-center space-x-1 text-left text-xs leading-4 font-medium cursor-pointer">

        <span> {{ $slot }}</span>

        <span>
            @if ($direction === 'asc')
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
            </svg>
            @elseif ($direction === 'desc')
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
            @endif
        </span>

    </button>

    @endif
</th>