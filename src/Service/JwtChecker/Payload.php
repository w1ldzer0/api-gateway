<?php

declare(strict_types=1);

namespace App\Service\JwtChecker;

class Payload
{
    private \DateTimeImmutable $expired;
    private array $roles = [];
    private int $id;
    private string $refreshToken;
    private string $username;

    public function __construct(array $payload)
    {
        $this->expired = (new \DateTimeImmutable())->setTimestamp($payload['exp']);
        $this->roles = $payload['roles'];
        $this->id = $payload['user_id'];
        $this->refreshToken = $payload['refresh_token'];
        $this->username = $payload['username'];
    }

    public function getExpired(): \DateTimeImmutable
    {
        return $this->expired;
    }

    /**
     * @return array|mixed
     */
    public function getRoles(): mixed
    {
        return $this->roles;
    }

    /**
     * @return int|mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @return mixed|string
     */
    public function getName(): mixed
    {
        return $this->name;
    }

    /**
     * @return array|mixed
     */
    public function getRefreshToken(): mixed
    {
        return $this->refreshToken;
    }

    /**
     * @return mixed|string
     */
    public function getUsername(): mixed
    {
        return $this->username;
    }
}
