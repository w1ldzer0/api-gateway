<?php

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

class PublicRequest extends Request implements RequestInterface
{
    private const AUTH_TOKEN_HEADER = 'Authorization';

    private const VERSION_TOKEN_HEADER = 'X-Accept-Version';

    private Request $nativeRequest;

    private string $apiVersion = '1.0';

    public function setNativeRequest(Request $request)
    {
        $this->nativeRequest = $request;
    }

    public function getNativeRequest(): Request
    {
        return $this->nativeRequest;
    }

    public function getUri(): string
    {
        return trim($this->nativeRequest->getPathInfo(), '/');
    }

    public function getAuthToken(): ?string
    {
        $token = explode(' ', $this->nativeRequest->headers->get(self::AUTH_TOKEN_HEADER));

        return $token[1] ?? null;
    }

    public function hasAuthToken(): bool
    {
        return $this->nativeRequest->headers->has(self::AUTH_TOKEN_HEADER);
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function setApiVersion(string $apiVersion): void
    {
        $this->apiVersion = $apiVersion;
    }
}
