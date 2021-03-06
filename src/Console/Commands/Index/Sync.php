<?php
namespace Triadev\Leopard\Console\Commands\Index;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Triadev\Leopard\Business\Helper\IsModelSearchable;
use Triadev\Leopard\Facade\Leopard;
use Triadev\Leopard\Searchable;

class Sync extends Command
{
    const MAX_CHUNK_SIZE = 1000;
    
    use IsModelSearchable;
    
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'triadev:index:sync {--index=}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the elasticsearch index with persist data.';
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $index = $this->option('index') ?: Leopard::getEsDefaultIndex();
        
        if (!Leopard::getEsClient()->indices()->exists(['index' => $index])) {
            $this->error(sprintf('The index does not exist: %s', $index));
            return;
        }
        
        $chunkSize = $this->getChunkSize();
        
        foreach ($this->getModelsToSync($index) as $modelClass => $model) {
            $this->line(sprintf('Sync index with model: %s', $modelClass));
            
            $model::chunk($chunkSize, function ($models) {
                /** @var Collection $models */
                Leopard::repository()->bulkSave($models);
                $this->line(sprintf('Indexing a chunk of %s documents ...', count($models)));
            });
        }
    }
    
    /**
     * @return int
     */
    private function getChunkSize() : int
    {
        $chunkSize = config('leopard.sync.chunkSize');
        
        return $chunkSize > self::MAX_CHUNK_SIZE ? self::MAX_CHUNK_SIZE : $chunkSize;
    }
    
    /**
     * @param string $index
     * @return Model|Searchable[]
     */
    private function getModelsToSync(string $index) : array
    {
        $models = [];
    
        foreach (config(sprintf('leopard.sync.models.%s', $index), []) as $modelClass) {
            /** @var Model|Searchable $model */
            $model = new $modelClass();
            if ($model instanceof Model) {
                $this->isModelSearchable($model);
                
                $models[$modelClass] = $model;
            }
        }
        
        return $models;
    }
}
