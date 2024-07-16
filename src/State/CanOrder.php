<?php

// src/Processor/OrderStatusProcessor.php

namespace App\State;

use App\Entity\Commande;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CanOrder implements ProcessorInterface
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof Commande) {
            throw new \InvalidArgumentException('Expected instance of ' . Commande::class);
        }

        $existingCommande = $this->entityManager->getRepository(Commande::class)
            ->findOneBy(['tableNumber' => $data->getTableNumber()]);

        if ($existingCommande) {
            return ('This table number is already in use.');
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
