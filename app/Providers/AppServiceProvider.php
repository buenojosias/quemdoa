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

        TallStackUi::customize()
            ->card()
            ->block('header.text.size', 'text-md font-semibold');
        TallStackUi::customize()
            ->card()
            ->block('header.text.color', 'text-red-900 dark:text-white underline decoration-red-900 dark:decoration-white');
        TallStackUi::customize()
            ->card()
            ->block('body')
            ->replace('text-secondary-700', 'text-slate-900');
        
        TallStackUi::customize()
            ->button()
            ->block('wrapper.sizes.md')
            ->replace('text-md', 'text-sm')
            ->and()
            ->button()
            ->block('wrapper.sizes.sm')
            ->replace('text-md', 'text-xs');

        TallStackUi::customize()
            ->modal()
            ->block('title.close')
            ->replace('text-secondary-300', 'text-secondary-500')
            ->and()
            ->slide()
            ->block('title.close')
            ->replace('text-secondary-300', 'text-secondary-500');
    }
}
