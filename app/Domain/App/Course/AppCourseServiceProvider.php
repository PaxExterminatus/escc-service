<?php

namespace App\Domain\App\Course;

use Illuminate\Support\ServiceProvider;

class AppCourseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
    }
}
