<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DirectusRestApi
{
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger) {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function callDirectusApi(string $url, string $method = 'GET', string $body = null, array $headers = null) {
        if ($headers == null)
            $headers = array();
        try {
            $httpresponse = $this->httpClient->request($method, $url, [
                'headers' => $headers,
                'body' => $body
            ]);
            $res = $httpresponse->getContent();
            return $res;
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->critical('Impossible d\'appeler l\'API ' . $url . " : " . $e);
        }
        return false;
    }

}