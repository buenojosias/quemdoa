<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use TallStackUi\Facades\TallStackUi;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        TallStackUi::customize()
            ->layout()
            ->block('main')
            ->replace('p-10', 'p-6 sm:p-8 lg:p-10');
    }
}
