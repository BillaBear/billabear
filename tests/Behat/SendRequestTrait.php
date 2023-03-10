<?php

namespace App\Tests\Behat;

trait SendRequestTrait
{
    public static ?string $apiKey = null;

    protected function authenticate(string $apiKey): void
    {
        static::$apiKey = $apiKey;
    }

    protected function sendJsonRequest(string $method, string $url, array $body = []): void
    {
        $jsonBody = json_encode($body);
        $this->session->visit('/');
        $components = parse_url($this->session->getCurrentUrl());
        $baseUrl = $components['scheme'].'://'.$components['host'].$url;
        $headers = [
            'Accept' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];
        $client = $this->session->getDriver()->getClient();
        $client->request(
            $method,
            $baseUrl,
            [],
            [],
            $headers,
            $jsonBody);
    }

    protected function getJsonContent(): array
    {
        $content = $this->session->getPage()->getContent();
        $json = json_decode($content, true);
        if (!$json) {
            throw new \Exception(sprintf('No valid JSON found. Got status code %s', $this->session->getStatusCode()));
        }

        return $json;
    }
}
