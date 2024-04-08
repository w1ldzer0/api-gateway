<?php

declare(strict_types=1);

namespace App\Service\JwtChecker;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Psr\Log\LoggerInterface;

class JwtChecker
{
    public function __construct(private JWTEncoderInterface $encoder, private LoggerInterface $logger)
    {
    }

    /**
     * Validate JWT token
     * All error save to log.
     */
    public function validate(?string $token): bool
    {
        try {
            $this->encoder->decode($token);

            return true;
        } catch (JWTDecodeFailureException $exception) {
            $this->logger->warning(sprintf('Incorrect token: %s. Error: %s', $token, $exception->getMessage()));

            return false;
        }
    }

    public function getPayload(?string $token): ?Payload
    {
        if (!$token or $token == 'null') {
            return null;
        }
        try {
            $payload = $this->encoder->decode($token);
            if ($payload === null) {
                return null;
            }

            return new Payload($payload);
        } catch (JWTDecodeFailureException $exception) {
            $this->logger->warning(sprintf('Incorrect token: %s. Error: %s', $token, $exception->getMessage()), ['getPayload']);

            return null;
        }
    }
}
