<?php

namespace App\Service\Proxier;

use App\Request\PublicRequest;
use App\Service\AppServicesManager\AppServicesRouting;
use App\Service\JwtChecker\JwtChecker;
use App\Service\RestClient;
use PrinsFrank\Standards\Http\HttpStatusCode;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Proxier
{
    public function __construct(
        protected RestClient $restClient,
        protected AppServicesRouting $router,
        protected LoggerInterface $logger,
        protected JwtChecker $jwtChecker)
    {
    }

    /**
     * Send request to certain service and provide response from there.
     */
    public function getResponse(PublicRequest $request): Response
    {
        $service = $this->router->matchService($request->getUri());
        if ($service === null) {
            return $this->createNotFoundResponse();
        }
        $request->setApiVersion($service->getActualVersion());

        try {
            $response = $this->restClient->send($request, $service->getHost());

            return $this->createResponse($response);
        } catch (ClientException $exception) {
            if ($this->isPassedHttpCode($exception->getCode())) {
                return $this->createResponse($response);
            }
            $this->logger->error(sprintf('Error in request to {%s}. Url: %s. Error: %s', $service->getName(), $response->getInfo('original_url'), $exception->getMessage()));

            return $this->createUnhandledError($response);
        } catch (\Throwable $exception) {
            $this->logger->error(sprintf('Error in request to {%s}. Url: %s. Error: %s', $service->getName(), $response->getInfo('original_url'), $exception->getMessage()));

            return $this->createUnhandledError($response);
        }
    }

    /**
     * Get response from service.
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function createResponse(ResponseInterface $response): Response
    {
        return new Response(
            $response->getContent(false),
            $response->getStatusCode(),
            $response->getHeaders(false)
        );
    }

    /**
     * Check http code for passing to response.
     */
    private function isPassedHttpCode(int $httpCode): bool
    {
        return ($httpCode - 400) < 100;
    }

    private function createNotFoundResponse(): Response
    {
        $errorPayload = [
            'errorCode' => HttpStatusCode::Not_Found,
            'message' => 'Route not found',
        ];

        return new Response(
            json_encode($errorPayload),
            404
        );
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function createUnhandledError(ResponseInterface $response): Response
    {
        $errorPayload = [
            'errorCode' => $response->getStatusCode(),
            'message' => 'Got error while requesting service',
        ];

        return new Response(
            json_encode($errorPayload),
            $response->getStatusCode(),
            $response->getHeaders(false)
        );
    }
}
