<?php

namespace App\Providers;

use App\Models\Ad;
use App\Models\Category;
use App\Models\Review;
use App\Policies\AdPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Ad::class => AdPolicy::class,
        Category::class => CategoryPolicy::class,
        Review::class => ReviewPolicy::class
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policies($this->policies);
    }
}
