<?php

// src/Processor/OrderStatusProcessor.php

namespace App\State;

use App\Entity\Commande;
use App\Entity\User;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\SecurityBundle\Security;

class OrderStatusProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $persistProcessor,
        private Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $id = $data->getId();
        $existingCommande = $this->entityManager->getRepository(Commande::class)
            ->findOneBy(['id' => $id]);
        $role = '';
        /** @var User $user */
        $user = $this->security->getUser();
        if($user->getId() != $existingCommande->getBartender()->getId()){
            if($user->getId() != $existingCommande->getWaiter()->getId()){
                return('pas ta table');
            } else {
                $role = $user->getRoles()[0];
            }
        } else {
            $role = $user->getRoles()[0];
        }

        if ($data->getStatus() == "payé") {
            return ("commande deja payé");
        } else if ($data->getStatus() == "prête" && $role == "ROLE_SERVEUR") {
            if ($data->getStatusTemp() == "payé") {

                $data->setStatus($data->getStatusTemp());
                $data->eraseCredentials();

                return $this->persistProcessor->process($data, $operation, $uriVariables, $context);;
            } else {
                return('mauvais changement de status');
            }
        } else if ($data->getStatus() == "en cours de préparation" && $role == "ROLE_BARMAN") {
            if ($data->getStatusTemp() == "prête") {

                $data->setStatus($data->getStatusTemp());
                $data->eraseCredentials();

                return $this->persistProcessor->process($data, $operation, $uriVariables, $context);;
            } else {
                return('mauvais changement de status');
            }
        }
    }
}
