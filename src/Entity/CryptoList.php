<?php

namespace App\Entity;

use App\Repository\CryptoListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CryptoListRepository::class)]
class CryptoList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $ApiCoin_id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $symbol;

    #[ORM\OneToMany(mappedBy: 'crypto_id', targetEntity: BalanceCoin::class)]
    private $balanceCoins;

    #[ORM\OneToMany(mappedBy: 'crypto_id', targetEntity: Historic::class)]
    private $historics;

    public function __construct()
    {
        $this->balanceCoins = new ArrayCollection();
        $this->historics = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApiCoinId(): ?int
    {
        return $this->ApiCoin_id;
    }

    public function setApiCoinId(int $ApiCoin_id): self
    {
        $this->ApiCoin_id = $ApiCoin_id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return Collection<int, BalanceCoin>
     */
    public function getBalanceCoins(): Collection
    {
        return $this->balanceCoins;
    }

    public function addBalanceCoin(BalanceCoin $balanceCoin): self
    {
        if (!$this->balanceCoins->contains($balanceCoin)) {
            $this->balanceCoins[] = $balanceCoin;
            $balanceCoin->setCryptoId($this);
        }

        return $this;
    }

    public function removeBalanceCoin(BalanceCoin $balanceCoin): self
    {
        if ($this->balanceCoins->removeElement($balanceCoin)) {
            // set the owning side to null (unless already changed)
            if ($balanceCoin->getCryptoId() === $this) {
                $balanceCoin->setCryptoId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Historic>
     */
    public function getHistorics(): Collection
    {
        return $this->historics;
    }

    public function addHistoric(Historic $historic): self
    {
        if (!$this->historics->contains($historic)) {
            $this->historics[] = $historic;
            $historic->setCryptoId($this);
        }

        return $this;
    }

    public function removeHistoric(Historic $historic): self
    {
        if ($this->historics->removeElement($historic)) {
            // set the owning side to null (unless already changed)
            if ($historic->getCryptoId() === $this) {
                $historic->setCryptoId(null);
            }
        }

        return $this;
    }
}
