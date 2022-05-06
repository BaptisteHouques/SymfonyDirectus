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

    public function getDirectusApi(string $url, string $method = 'GET', string $body = null, array $headers = []) {
        if ($headers == null)
            $headers = array();
        try {
            $httpresponse = $this->httpClient->request($method, $url, [
                'headers' => $headers,
                'body' => $body
            ]);
            $res = $httpresponse->getContent();
            $res = json_decode($res);
            return $res->data;
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->critical('Impossible d\'appeler l\'API ' . $url . " : " . $e);
        }
        return false;
    }

    public function removeRelation (string $url, string $body = null, array $headers = null) {
        if ($headers == null)
            $headers = array();
        $body = json_encode($body);
        $headers = ["Authorization" => "Bearer t", "Content-Type" => "application/json"];
        try {
            $httpresponse = $this->httpClient->request('PATCH', $url,[
                'headers' => $headers,
                'body' => $body
            ]);
            return $httpresponse->getContent();
        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->critical('Impossible d\'appeler l\'API ' . $url . " : " . $e);
        }
        return false;
    }

    public function directusGraphQL(string $url, string $query, string $method = 'POST', string $body = null, array $headers = []) {
        if ($headers == null)
            $headers = array();
        try {
            $data = array('query' => $query);
            $data = json_encode($data);
            $headers = ['Content-Type' => 'application/json'];

            $httpresponse = $this->httpClient->request($method, $url, [
                'headers' => $headers,
                'body' => $data
            ]);
            $res = $httpresponse->getContent();

            $result = json_decode($res);
            return $result->data->bloc_information;

        } catch (ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | TransportExceptionInterface $e) {
            $this->logger->critical('Impossible d\'appeler l\'API ' . $url . " : " . $e);
        }
        return false;
    }
}