<?php

namespace App\Entity;

use App\Repository\BalanceCoinRepository;
use App\Repository\CryptoListRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BalanceCoinRepository::class)]
class BalanceCoin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'float')]
    private $quantity;

    #[ORM\Column(type: 'datetime')]
    private $date;

    #[ORM\Column(type: 'boolean')]
    private $actionClosed;

    #[ORM\ManyToOne(targetEntity: CryptoList::class, inversedBy: 'balanceCoins')]
    #[ORM\JoinColumn(nullable: false)]
    private $crypto_id;

    #[ORM\Column(type: 'float')]
    private $quote;

    #[ORM\Column(type: 'float')]
    private $latestQuote;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getActionClosed(): ?bool
    {
        return $this->actionClosed;
    }

    public function setActionClosed(bool $actionClosed): self
    {
        $this->actionClosed = $actionClosed;

        return $this;
    }

    public function getCryptoId(): ?CryptoList
    {
        return $this->crypto_id;
    }

    public function setCryptoId(?CryptoList $crypto_id): self
    {
        $this->crypto_id = $crypto_id;

        return $this;
    }

    public function getQuote(): ?float
    {
        return $this->quote;
    }

    public function setQuote(float $quote): self
    {
        $this->quote = $quote;

        return $this;
    }

    public function getLatestQuote(): ?float
    {
        return $this->latestQuote;
    }

    public function setLatestQuote(float $latestQuote): self
    {
        $this->latestQuote = $latestQuote;

        return $this;
    }
}
