<?php

namespace App\Entity;

use App\Repository\BoissonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;

use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BoissonRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to get boisson'),
        new Post(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to get this boisson'),
        new Get(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to get this boisson'),
        new Put(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to edit this boisson'),
        new Patch( security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to edit this boisson'),
        new Delete(security: "is_granted('ROLE_BARMAN')", securityMessage: 'You are not allowed to delete this boisson'),
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
    forceEager: false
)]
class Boisson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('read')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read', 'write'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['read', 'write'])]
    private ?float $price = null;

    #[ORM\OneToOne(inversedBy: 'boisson', cascade: ['persist', 'remove'])]
    #[Groups(['read', 'write'])]
    private ?Media $media = null;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\ManyToMany(targetEntity: Commande::class, mappedBy: 'boissons')]
    #[Groups(['read', 'write'])]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        // unset the owning side of the relation if necessary
        if ($media === null && $this->media !== null) {
            $this->media->setBoisson(null);
        }

        // set the owning side of the relation if necessary
        if ($media !== null && $media->getBoisson() !== $this) {
            $media->setBoisson($this);
        }

        $this->media = $media;

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->addBoisson($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            $commande->removeBoisson($this);
        }

        return $this;
    }

}
