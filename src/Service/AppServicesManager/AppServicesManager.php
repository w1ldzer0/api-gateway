<?php

namespace App\Service\AppServicesManager;

use App\Exception\IncorrectConfigException;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class AppServicesManager
{
    private const APP_SERVICE_CONFIG_KEY = 'app-services';
    private const CONFIG_HOST_KEY = 'host';
    private const CONFIG_VERSION_KEY = 'actual_version';
    private const CONFIG_ROUTES_KEY = 'routes';
    private const CONFIG_PATTERN_KEY = 'pattern';
    private const CONFIG_AUTH_KEY = 'auth';
    private const CONFIG_SWAGGER_KEY = 'swagger';
    private const CONFIG_NEDD_USER_ID_KEY = 'need_user_id';
    /**
     * @var AppServiceItem[]
     */
    private array $services = [];

    public function __construct(ContainerBagInterface $containerBag)
    {
        if (!$containerBag->has(self::APP_SERVICE_CONFIG_KEY)) {
            throw new IncorrectConfigException('Can\'t find service map config');
        }
        $services = $containerBag->get(self::APP_SERVICE_CONFIG_KEY);
        foreach ($services as $serviceName => $service) {
            $serviceItem = new AppServiceItem();
            $serviceItem->setName($serviceName)
                ->setHost($service[self::CONFIG_HOST_KEY])
                ->setActualVersion($service[self::CONFIG_VERSION_KEY]);
            if (isset($service[self::CONFIG_SWAGGER_KEY])) {
                $serviceItem->setSwagger($service[self::CONFIG_SWAGGER_KEY]);
            }
            foreach ($service[self::CONFIG_ROUTES_KEY] as $route) {
                $routeItem = new AppServiceRouteItem();
                $routeItem->setAuth($route[self::CONFIG_AUTH_KEY] ?? true)
                    ->setPattern($route[self::CONFIG_PATTERN_KEY]);
                if (isset($route[self::CONFIG_NEDD_USER_ID_KEY])) {
                    $routeItem->setNeedUserId($route[self::CONFIG_NEDD_USER_ID_KEY]);
                }
                $serviceItem->addRoutes($routeItem);
            }
            $this->services[] = $serviceItem;
        }
    }

    /**
     * @return AppServiceItem[]
     */
    public function getServices(): array
    {
        return $this->services;
    }
}
