<?php

namespace App\Entity;

use App\Entity\Boisson;
use App\State\CanOrder;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use App\State\OrderStatusProcessor;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\CommandeRepository;
use ApiPlatform\Metadata\GetCollection;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'status' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['creationDate' => DateFilter::EXCLUDE_NULL])]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')", securityMessage: 'You are not allowed to get users'),
        new Post(security: "is_granted('ROLE_SERVEUR')", processor:CanOrder::class, securityMessage:'You are not allowed to take commande'),
        new Get(security: "is_granted('ROLE_SERVEUR')", securityMessage: 'You are not allowed to get this user'),
        // new Patch(security: "is_granted('ROLE_SERVEUR')", processor:OrderStatusProcessorWaiter::class, securityMessage: 'You are not allowed to edit this user'),
        new Patch(security: "is_granted('ROLE_BARMAN') or is_granted('ROLE_SERVEUR')", processor:OrderStatusProcessor::class, securityMessage: 'You are not allowed to edit this user')
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    forceEager: false
)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['read'])]
    private ?\DateTimeInterface $creationDate = null;

    /**
     * @var Collection<int, Boisson>
     */
    #[ORM\ManyToMany(targetEntity: Boisson::class, inversedBy: 'commandes')]
    #[Groups(['read', 'write'])]
    private Collection $boissons;

    #[ORM\Column]
    #[Groups(['read', 'write'])]
    private ?int $tableNumber = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[Groups(['read', 'write'])]
    private ?User $waiter = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[Groups(['read', 'write'])]
    private ?User $bartender = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read'])]
    private ?string $status = null;

    #[Groups(['write'])]
    private ?string $statusTemp = null;

    public function __construct()
    {
        $this->creationDate = new \DateTime();
        $this->boissons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * @return Collection<int, Boisson>
     */
    public function getBoissons(): Collection
    {
        return $this->boissons;
    }

    public function addBoisson(Boisson $boisson): static
    {
        // if (!$this->boissons->contains($boisson)) {
        //     $this->boissons->add($boisson);
        // }

        $this->boissons->add($boisson);

        return $this;
    }

    public function removeBoisson(Boisson $boisson): static
    {
        $this->boissons->removeElement($boisson);

        return $this;
    }

    public function getTableNumber(): ?int
    {
        return $this->tableNumber;
    }

    public function setTableNumber(int $tableNumber): static
    {
        $this->tableNumber = $tableNumber;

        return $this;
    }

    public function getWaiter(): ?User
    {
        return $this->waiter;
    }

    public function setWaiter(?User $waiter): static
    {
        $this->waiter = $waiter;

        return $this;
    }

    public function getBartender(): ?User
    {
        return $this->bartender;
    }

    public function setBartender(?User $bartender): static
    {
        $this->bartender = $bartender;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusTemp(): string
    {
        return $this->statusTemp;
    }

    public function setStatusTemp(string $statusTemp): static
    {
        $this->statusTemp = $statusTemp;

        return $this;
    }

        /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->statusTemp = null;
    }
}
