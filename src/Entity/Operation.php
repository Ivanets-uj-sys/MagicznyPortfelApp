<?php

/**
 * Operation entity.
 */

namespace App\Entity;

use App\Repository\OperationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class operation.
 */
#[ORM\Entity(repositoryClass: OperationRepository::class)]
#[ORM\Table(name: 'operations')]
#[ORM\UniqueConstraint(name: 'uq_operation_title', columns: ['title'])]
#[UniqueEntity(fields: ['title'])]
class Operation
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Title.
     */
    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 64)]
    private ?string $title = null;

    /**
     * Created at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Updated at.
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Amount.
     */
    #[ORM\Column(type: 'float')]
    private ?float $amount = null;

    /**
     * Balance.
     */
    #[ORM\Column(type: 'float')]
    private ?float $balance = null;

    /**
     * Operaton description.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $operationDescription = null;

    /**
     * Category.
     */
    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'operations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;
    /**
     * Slug.
     */
    #[ORM\Column(length: 64, nullable: true)]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 64)]
    #[Gedmo\Slug(fields: ['title'])]
    private ?string $slug = null;
    /**
     * Tags.
     *
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'operations', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable(name: 'operation_tags')]
    #[Assert\Valid]
    private Collection $tags;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * Wallet.
     */
    #[ORM\ManyToOne(targetEntity: Wallet::class, inversedBy: 'operations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wallet $wallet = null;

    /**
     * Getter for Id.
     *
     * @return int|null Id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for title.
     *
     * @return string|null Title
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Setter for title.
     *
     * @param string|null $title Title
     *
     * @return $this
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * Getter for created at.
     *
     * @return \DateTimeImmutable|null Created at
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for created at.
     *
     * @param \DateTimeImmutable|null $createdAt Created at
     *
     * @return $this
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Getter for updated at.
     *
     * @return \DateTimeImmutable|null Updated at
     */
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Setter for updated at.
     *
     * @param \DateTimeImmutable|null $updatedAt Updated at
     *
     * @return $this
     */
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Getter for amount.
     *
     * @return float|null Amount
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * Setter for amount.
     *
     * @param float|null $amount Amount
     *
     * @return $this
     */
    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Getter for balance.
     *
     * @return float|null Balance
     */
    public function getBalance(): ?float
    {
        return $this->balance;
    }

    /**
     * Setter for balance.
     *
     * @param float|null $balance Balance
     *
     * @return $this
     */
    public function setBalance(float $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Getter for Operation Description.
     *
     * @return string|null Operation Description
     */
    public function getOperationDescription(): ?string
    {
        return $this->operationDescription;
    }

    /**
     * Setter for Operation Description.
     *
     * @param string $operationDescription Operation Description
     *
     * @return $this
     */
    public function setOperationDescription(string $operationDescription): static
    {
        $this->operationDescription = $operationDescription;

        return $this;
    }

    /**
     * Getter for Category.
     *
     * @return Category|null Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * Setter for Category.
     *
     * @param Category|null $category Category
     *
     * @return $this
     */
    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Getter for tags.
     *
     * @return Collection<int, Tag> Tags collection
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * Add tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return $this
     */
    public function addTag(Tag $tag): void
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addOperation($this);
        }
    }

    /**
     * Remove Tag.
     *
     * @param Tag $tag Tag entity
     *
     * @return $this
     */
    public function removeTag(Tag $tag): void
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeOperation($this);
        }
    }

    /**
     * Getter for slug.
     *
     * @return string|null Slug
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * Setter for slug.
     *
     * @param string|null $slug Slug
     *
     * @return $this
     */
    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Getter for Wallet.
     *
     * @return Wallet|null $wallet Wallet
     */
    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    /**
     * Setter for Wallet.
     *
     * @param Wallet|null $wallet $wallet Wallet
     *
     * @return $this
     */
    public function setWallet(?Wallet $wallet): static
    {
        $this->wallet = $wallet;

        return $this;
    }
}
