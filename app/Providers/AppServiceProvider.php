<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Image;
use App\Models\Project;
use App\Models\Tool;
use App\Policies\CategoryPolicy;
use App\Policies\ImagePolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ToolPolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Image::class => ImagePolicy::class,
        Tool::class => ToolPolicy::class,
        Category::class => CategoryPolicy::class,
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
        //
    }
}
