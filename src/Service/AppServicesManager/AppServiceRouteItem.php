<?php

namespace App\Service\AppServicesManager;

class AppServiceRouteItem
{
    private string $pattern;
    private bool $auth = true;
    private bool $needUserId = false;

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): AppServiceRouteItem
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function isAuth(): bool
    {
        return $this->auth;
    }

    public function setAuth(bool $auth): AppServiceRouteItem
    {
        $this->auth = $auth;

        return $this;
    }

    public function isNeedUserId(): bool
    {
        return $this->needUserId;
    }

    public function setNeedUserId(bool $needUserId): AppServiceRouteItem
    {
        $this->needUserId = $needUserId;

        return $this;
    }
}
