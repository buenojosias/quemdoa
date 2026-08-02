@props(['label', 'value' => '', 'is_badge' => false, 'badge_color' => null])

<div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4 text-sm">
    <div class="text-gray-500 dark:text-gray-400">{{ $label }}</div>
    <div class="col-span-2 text-gray-700 dark:text-gray-300 font-medium">
        @if ($is_badge)
            <x-badge :text="$value" :color="$badge_color" />
        @else
            {{ $value }}
        @endif
    </div>
</div>