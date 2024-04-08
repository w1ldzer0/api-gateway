<?php

namespace App\Service\AppServicesManager;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppServicesRouting
{
    private array $matchedServices = [];
    private array $matchedRoutes = [];
    private const CACHE_ROUTING_MAP = 'routing_map';

    public function __construct(private AppServicesManager $manager,
                                private LoggerInterface $logger,
                                private HttpClientInterface $client,
                                private CacheInterface $cache)
    {
    }

    public function matchService(string $path): ?AppServiceItem
    {
        if (isset($this->matchedServices[$path])) {
            return $this->matchedServices[$path];
        }
        [$service, $route] = $this->getMatched($path);

        return $service;
    }

    public function matchRoute(string $path): ?AppServiceRouteItem
    {
        if (isset($this->matchedRoutes[$path])) {
            return $this->matchedRoutes[$path];
        }
        [$service, $route] = $this->getMatched($path);

        return $route;
    }

    private function getMatched(string $path): array
    {
        $services = $this->manager->getServices();
        foreach ($services as $service) {
            foreach ($service->getRoutes() as $route) {
                preg_match($route->getPattern(), $path, $match);
                if ($match) {
                    $this->matchedRoutes[$path] = $route;
                    $this->matchedServices[$path] = $service;

                    return [$service, $route];
                }
            }
        }

        return [null, null];
    }

    public function getRoutes(): ?array
    {
        return $this->cache->get(self::CACHE_ROUTING_MAP, function (ItemInterface $item) {
            $item->expiresAfter(new \DateInterval('P1D'));
            $services = $this->manager->getServices();
            $data = [];
            foreach ($services as $service) {
                try {
                    $response = $this->client->request('GET', $service->getHost() . $service->getSwagger());
                    $content = json_decode($response->getContent(), true);
                } catch (\Exception $exception) {
                    $this->logger->error(sprintf('Error while getting service swagger. %s', $exception->getMessage()), [
                        'service' => $service->getName(),
                        'exception' => $exception,
                    ]);

                    return null;
                }
                if (!isset($data[$service->getName()])) {
                    $data[$service->getName()] = [];
                }
                foreach ($content['paths'] as $path => $pathData) {
                    $data[$service->getName()][$path] = array_keys($pathData);
                }
            }

            return $data;
        });
    }
}
