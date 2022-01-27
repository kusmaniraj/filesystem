<?php

namespace LogisticMall\FileSystem;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageServiceProvider;


class LMFileSystemServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->app->alias( 'Intervention\Image\Facades\Image','Image');



    }
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__ . '/../config/filesystems.php' => base_path('config/filesystems.php'),
        ]);
//        $this->app->make('LogisticMall\FileSystem\LMFileSystem');

        $this->app->register(ImageServiceProvider::class);
        $this->app->bind('filesystem',function (){
            return new LMFileSystem();
        });

    }
}
