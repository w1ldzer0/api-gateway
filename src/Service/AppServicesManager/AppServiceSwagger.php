<?php

namespace App\Service\AppServicesManager;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppServiceSwagger
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

    public function getCompiledSwagger(): ?array
    {
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
    }
}
