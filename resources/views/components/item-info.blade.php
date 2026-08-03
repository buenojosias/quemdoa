@props(['label', 'value' => null, 'is_badge' => false, 'badge_color' => null])

<div class="grid grid-cols-1 sm:grid-cols-2 gap-1 sm:gap-4 text-sm">
    <div class="text-gray-500 dark:text-gray-400 font-medium">{{ $label }}</div>
    <div class="text-gray-700 dark:text-gray-300 font-medium">
        @if ($is_badge)
            <x-badge :text="$value" :color="$badge_color" />
        @elseif ($slot->isNotEmpty())
            {{ $slot }}
        @else
            {{ $value }}
        @endif
    </div>
</div>