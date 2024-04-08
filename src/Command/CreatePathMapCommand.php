<?php

namespace App\Command;

use App\Service\AppServicesManager\AppServicesRouting;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'api-gateway:create-path-map')]
class CreatePathMapCommand extends Command
{
    public function __construct(private AppServicesRouting $servicesRouting,
                                private LoggerInterface $logger,
                                private HttpClientInterface $client,
                                string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($routes = $this->servicesRouting->getRoutes()) {
            dump($routes);

            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
