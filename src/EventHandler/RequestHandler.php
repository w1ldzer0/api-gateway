<?php

declare(strict_types=1);

namespace App\EventHandler;

use App\Request\PublicRequest;
use App\Request\RequestInterface;
use App\Service\AppServicesManager\AppServiceItem;
use App\Service\AppServicesManager\AppServiceRouteItem;
use App\Service\AppServicesManager\AppServicesRouting;
use App\Service\JwtChecker\JwtChecker;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(RequestEvent::class)]
final class RequestHandler
{
    private const USER_ID_HEADER = 'HTTP_X_USER_ID';

    public function __construct(
        private AppServicesRouting $router,
        private JwtChecker $jwtChecker,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $publicRequest = new PublicRequest();
        $publicRequest->setNativeRequest($event->getRequest());

        $this->logger->info(sprintf('Handle request to %s', $publicRequest->getUri()));
        $service = $this->router->matchService($publicRequest->getUri());
        if ($service === null) {
            return;
        }
        $route = $this->router->matchRoute($publicRequest->getUri());
        $this->logger->info(sprintf('Service: %s, route: %s', $service->getName(), $route->getPattern()));
        if ($route->isAuth()) {
            $this->checkAuth($service, $publicRequest, $event);
        }
        $this->addUserToBody($publicRequest, $event, $route);
    }

    private function checkAuth(AppServiceItem $service, RequestInterface $request, RequestEvent $event)
    {
        $validated = $this->jwtChecker->validate($request->getAuthToken());
        if (!$validated) {
            $this->logger->error(sprintf('Error in request to {%s}', $service->getName()));

            $event->setResponse($this->createForbiddenResponse());
        }
    }

    private function addUserToBody(RequestInterface $request, RequestEvent $event, AppServiceRouteItem $route)
    {
        $payload = $this->jwtChecker->getPayload($request->getAuthToken());
        if ($payload === null) {
            return;
        }
        $serverParams = $event->getRequest()->server->all();

        $content = json_decode($request->getNativeRequest()->getContent(), true);
        if ($content === null) {
            $content = [];
        }
        $serverParams[self::USER_ID_HEADER] = (string) $payload->getId();
        if (!$route->isNeedUserId()) {
            $this->setNewRequest($event, $content, $serverParams);

            return;
        }

        if (isset($content['user_id'])) {
            $this->setNewRequest($event, $content, $serverParams);

            return;
        }
        $content['user_id'] = $payload->getId();
        $this->setNewRequest($event, $content, $serverParams);
    }

    private function setNewRequest(RequestEvent $event, array $content, array $serverParams)
    {
        $event->getRequest()->initialize(
            $event->getRequest()->query->all(),
            $event->getRequest()->request->all(),
            $event->getRequest()->attributes->all(),
            $event->getRequest()->cookies->all(),
            $event->getRequest()->files->all(),
            $serverParams,
            json_encode($content)
        );
    }

    /**
     * Create response for forbidden response.
     */
    public function createForbiddenResponse(): Response
    {
        $errorPayload = [
            'errorCode' => Response::HTTP_FORBIDDEN,
            'message' => sprintf('Request was forbidden'),
        ];

        return new Response(
            json_encode($errorPayload),
            Response::HTTP_FORBIDDEN
        );
    }
}
