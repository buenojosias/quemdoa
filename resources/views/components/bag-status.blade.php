@props(['bag'])

@php
    $text = match (true) {
        filled($bag->received_at) => 'Recebida',
        filled($bag->confirmed_at) => 'Confirmada',
        default => 'Pendente',
    };

    $color = match (true) {
        filled($bag->received_at) => 'green',
        filled($bag->confirmed_at) => 'blue',
        default => 'yellow',
    };
@endphp

<x-badge :text="$text" :color="$color" light {{ $attributes }} />
