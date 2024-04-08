<?php

namespace App\Service;

use App\Exception\IncorrectConfigException;
use App\Request\RequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RestClient
{
    protected const MULTIPART_FORM = 'form';
    protected const JSON = 'json';

    public function __construct(private HttpClientInterface $client, private LoggerInterface $logger)
    {
    }

    public function send(RequestInterface $request, string $uri): ResponseInterface
    {
        $method = $request->getNativeRequest()->getMethod();
        $response = match ($method) {
            Request::METHOD_GET => $this->get($uri, $request),
            Request::METHOD_POST,
            Request::METHOD_DELETE,
            Request::METHOD_PATCH => $this->post($uri, $request, $method),
            default => throw new IncorrectConfigException(sprintf('Incorrect method %s', $method)),
        };

        return $response;
    }

    private function get(string $uri, RequestInterface $request): ResponseInterface
    {
        $params = [];
        $params['body'] = $request->getNativeRequest()->getContent();
        $params['headers'] = $this->getInjectedHeaders($request);
        $this->logger->info('Request params', ['uri' => $uri, 'params' => $params]);

        return $this->client->request(
            'GET',
            $uri . $request->getNativeRequest()->getRequestUri(),
            $params
        );
    }

    private function post(string $uri, RequestInterface $request, string $method): ResponseInterface
    {
        if ($request->getNativeRequest()->getContentType() === self::MULTIPART_FORM) {
            $formFields = $request->getNativeRequest()->request->all();

            /** @var UploadedFile $uploadedFile */
            foreach ($request->getNativeRequest()->files->all() as $key => $uploadedFile) {
                $formFields[$key] = new DataPart(
                    $uploadedFile->getContent(),
                    $uploadedFile->getClientOriginalName(),
                    $uploadedFile->getMimeType()
                );
            }

            $formData = new FormDataPart($formFields);

            $params = [
                'headers' => $this->getInjectedHeaders($request, $formData),
                'body' => $formData->bodyToIterable(),
            ];

            $this->logger->info('Post request form', $params);

            return $this->client->request(
                $method,
                $uri . $request->getNativeRequest()->getRequestUri(),
                $params
            );
        }

        $params = [];
        $params['body'] = $request->getNativeRequest()->getContent();
        $params['headers'] = $this->getInjectedHeaders($request);
        $this->logger->info('Post request', $params);

        return $this->client->request(
            $method,
            $uri . $request->getNativeRequest()->getRequestUri(),
            $params
        );
    }

    private function getInjectedHeaders(RequestInterface $request, ?FormDataPart $formData = null): array
    {
        $originalHeaders = [
            'Authorization' => $request->getNativeRequest()->headers->get('Authorization'),
            'X-User-Id' => $request->getNativeRequest()->headers->get('x-user-id'),
        ];

        $contentType = 'application/json';

        if ($formData !== null) {
            $formContentType = $formData->getPreparedHeaders()->get('Content-Type');

            $contentType = $formContentType->getBodyAsString() ?? 'application/json';
        }

        return array_merge($originalHeaders, [
            'X-Client-Ip' => $request->getNativeRequest()->getClientIp(),
            'Content-Type' => $contentType,
            'Accept' => 'application/json',
            'X-Accept-Version' => $request->getApiVersion(),
        ]);
    }
}
