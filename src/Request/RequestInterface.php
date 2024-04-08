<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\Request;

interface RequestInterface
{
    public function getUri(): string;

    public function getNativeRequest(): Request;

    public function getApiVersion(): string;
}
