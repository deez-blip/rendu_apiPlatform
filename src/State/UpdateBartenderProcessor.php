<?php
// src/State/UpdateBartenderProcessor.php

namespace App\State;

use App\Entity\Commande;
use App\Entity\User;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateBartenderProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        /** @var Commande $existingCommande */
        $existingCommande = $this->entityManager->getRepository(Commande::class)->find($uriVariables['id']);

        if (!$existingCommande) {
            return new JsonResponse(['message' => 'Commande not found.'], JsonResponse::HTTP_NOT_FOUND);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        // Check if the current bartender is already the same
        if ($existingCommande->getBartender() && $existingCommande->getBartender()->getId() === $user->getId()) {
            return new JsonResponse(['message' => 'The user is already the bartender of this order.'], JsonResponse::HTTP_OK);
        }

        // Allow ROLE_BARMAN to set himself as bartender
        if (in_array('ROLE_BARMAN', $user->getRoles())) {
            $existingCommande->setBartender($user);
            $this->persistProcessor->process($existingCommande, $operation, $uriVariables, $context);
            return new JsonResponse(['message' => 'Bartender updated successfully.'], JsonResponse::HTTP_OK);
        } else {
            return new JsonResponse(['message' => 'Only a barman can update the bartender.'], JsonResponse::HTTP_FORBIDDEN);
        }
    }
}
