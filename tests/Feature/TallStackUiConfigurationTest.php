<?php

it('orders TallStackUI overlay z-indexes from slide to toast', function () {
    expect(config('ts-ui.components.slide.1.z-index'))->toBe('z-40')
        ->and(config('ts-ui.components.modal.1.z-index'))->toBe('z-50')
        ->and(config('ts-ui.components.dialog.1.z-index'))->toBe('z-60')
        ->and(config('ts-ui.components.toast.1.z-index'))->toBe('z-70');
});
