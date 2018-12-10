<?php
namespace Triadev\Es\ODM\Provider;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Triadev\Es\ODM\Business\Repository\ElasticsearchRepository;
use Triadev\Es\ODM\Contract\ElasticsearchManagerContract;
use Triadev\Es\ODM\Contract\Repository\ElasticsearchRepositoryContract;
use Triadev\Es\ODM\ElasticsearchManager;
use Triadev\Es\ODM\Facade\EsManager;
use Triadev\Es\Provider\ElasticsearchServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__ . '/../Config/config.php');
    
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('triadev-elasticsearch-odm.php'),
        ], 'config');
    
        $this->mergeConfigFrom($source, 'triadev-elasticsearch-odm');
        
        $this->app->bind(
            ElasticsearchRepositoryContract::class,
            ElasticsearchRepository::class
        );
    }
    
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(ElasticsearchServiceProvider::class);
    
        $this->app->singleton(ElasticsearchManagerContract::class, function () {
            return app()->make(ElasticsearchManager::class);
        });
        
        AliasLoader::getInstance()->alias('EsManager', EsManager::class);
    }
}
