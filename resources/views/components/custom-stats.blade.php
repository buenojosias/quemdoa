@props([
    'title',
    'number',
    'icon',
    'color' => 'primary',
    'link_label' => 'Ver todas',
    'link_url' => '#',
    'description' => null,
])

<x-card class="shadow-sm">
    <div class="flex items-center gap-5">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-{{ $color }}-100 text-{{ $color }}-600 dark:bg-{{ $color }}-500/15 dark:text-{{ $color }}-300">
            <x-icon :name="$icon" class="h-7 w-7" />
        </div>
        <div>
            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ $title }}</p>
            <p class="mt-1 text-3xl font-bold text-slate-950 dark:text-white">{{ $number }}</p>
        </div>
    </div>
    <a href="{{ $link_url }}" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-teal-700 hover:text-teal-800 dark:text-teal-300 dark:hover:text-teal-200">
        {{ $link_label }}
        <x-icon name="arrow-right" class="h-4 w-4" />
    </a>
    @if ($description)
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif
</x-card>