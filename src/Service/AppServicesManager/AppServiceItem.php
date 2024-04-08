<?php

namespace App\Service\AppServicesManager;

class AppServiceItem
{
    private string $name;
    private string $host;
    private string $actualVersion;
    private string $swagger = '/api/doc.json';
    /**
     * @var AppServiceRouteItem[]
     */
    private array $routes = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): AppServiceItem
    {
        $this->name = $name;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): AppServiceItem
    {
        $this->host = $host;

        return $this;
    }

    public function getActualVersion(): string
    {
        return $this->actualVersion;
    }

    public function setActualVersion(string $actualVersion): AppServiceItem
    {
        $this->actualVersion = $actualVersion;

        return $this;
    }

    public function addRoutes(AppServiceRouteItem $route): AppServiceItem
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * @return AppServiceRouteItem[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getSwagger(): string
    {
        return $this->swagger;
    }

    public function setSwagger(string $swagger): AppServiceItem
    {
        $this->swagger = $swagger;

        return $this;
    }
}
