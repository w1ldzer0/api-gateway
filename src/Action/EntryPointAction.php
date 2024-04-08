<?php

declare(strict_types=1);

namespace App\Action;

use App\Request\PublicRequest;
use App\Service\Proxier\Proxier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'public_request')]
final class EntryPointAction
{
    public function __construct(
        private Proxier $proxier,
    ) {
    }

    public function __invoke(Request $request)
    {
        $proxiedPublicRequest = new PublicRequest();
        $proxiedPublicRequest->setNativeRequest($request);

        return $this->proxier->getResponse($proxiedPublicRequest);
    }
}
