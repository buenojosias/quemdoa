@props(['label', 'value'])

<div class="grid grid-cols-1 md:grid-cols-3 gap-1 md:gap-4 text-sm">
    <div class="text-gray-500 dark:text-gray-400">{{ $label }}</div>
    <div class="col-span-2 text-gray-700 dark:text-gray-300 font-medium">{{ $value }}</div>
</div>