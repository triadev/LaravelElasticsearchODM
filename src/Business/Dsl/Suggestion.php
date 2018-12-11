<?php
namespace Triadev\Es\ODM\Business\Dsl;

use ONGR\ElasticsearchDSL\Suggest\Suggest;
use Triadev\Es\ODM\Contract\ElasticsearchManagerContract;
use Triadev\Es\ODM\Facade\EsManager;

class Suggestion
{
    /** @var \ONGR\ElasticsearchDSL\Search */
    private $search;
    
    /** @var ElasticsearchManagerContract */
    private $manager;
    
    /**
     * Suggestion constructor.
     * @param ElasticsearchManagerContract $manager
     * @param \ONGR\ElasticsearchDSL\Search|null $search
     */
    public function __construct(
        ElasticsearchManagerContract $manager,
        ?\ONGR\ElasticsearchDSL\Search $search = null
    ) {
        $this->manager = $manager;
        $this->search = $search ?: new \ONGR\ElasticsearchDSL\Search();
    }
    
    /**
     * To dsl
     *
     * @return array
     */
    public function toDsl(): array
    {
        return $this->search->toArray();
    }
    
    /**
     * Get
     *
     * @param string $index
     * @return array
     */
    public function get(string $index): array
    {
        return EsManager::suggestStatement([
            'index' => $index,
            'body' => $this->toDsl()
        ]);
    }
    
    /**
     * Term
     *
     * @param string $name
     * @param string $text
     * @param string $field
     * @param array $params
     * @return Suggestion
     */
    public function term(string $name, string $text, string $field, array $params = []): Suggestion
    {
        $this->search->addSuggest(new Suggest(
            $name,
            'term',
            $text,
            $field,
            $params
        ));
        
        return $this;
    }
    
    /**
     * Phrase
     *
     * @param string $name
     * @param string $text
     * @param string $field
     * @param array $params
     * @return Suggestion
     */
    public function phrase(string $name, string $text, string $field, array $params = []): Suggestion
    {
        $this->search->addSuggest(new Suggest(
            $name,
            'phrase',
            $text,
            $field,
            $params
        ));
        
        return $this;
    }
    
    /**
     * Term
     *
     * @param string $name
     * @param string $text
     * @param string $field
     * @param array $params
     * @return Suggestion
     */
    public function completion(string $name, string $text, string $field, array $params = []): Suggestion
    {
        $this->search->addSuggest(new Suggest(
            $name,
            'completion',
            $text,
            $field,
            $params
        ));
        
        return $this;
    }
}