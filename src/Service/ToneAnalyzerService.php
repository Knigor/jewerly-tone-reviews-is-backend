<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ToneAnalyzerService
{
    private HttpClientInterface $httpClient;


    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function analyze(string $text): ?int
    {
        $response = $this->httpClient->request('POST', 'http://host.docker.internal:8000/analyze', [
            'json' => ['text' => $text],
        ]);

        $data = $response->toArray();

        return $data['sentiment'] ?? null;
    }
}
