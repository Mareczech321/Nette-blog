<?php

namespace App\Model;

use Elastic\Elasticsearch\ClientBuilder;
use Elastic\Elasticsearch\Client;

class ElasticSearchFacade
{
    private Client $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->setHosts(['elasticsearch:9200'])
            ->build();
    }

    /**
     * @return array<mixed>
     */
    public function searchByTitle(string $query): array
    {
        $params = [
            'index' => 'articles',
            'body'  => [
                'query' => [
                    'wildcard' => [
                        'title' => [
                            'value' => '*' . mb_strtolower($query) . '*',
                            'boost' => 1.0
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->client->search($params);

        assert($response instanceof \Elastic\Elasticsearch\Response\Elasticsearch);

        $responseArray = $response->asArray();

        return $responseArray['hits']['hits'] ?? [];
    }

    public function indexArticle(int $id, string $title, string $content, ?string $image = null): void
    {
        $params = [
            'index' => 'articles',
            'id'    => $id, // Důležité: Použijeme ID z MySQL, abychom to uměli propojit
            'body'  => [
                'title'   => $title,
                'content' => $content,
                'image'   => $image
            ]
        ];

        // Odešle data do Elasticsearch (pokud index 'articles' neexistuje, ES ho sám vytvoří)
        $this->client->index($params);
    }
}