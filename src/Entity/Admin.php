<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: '`admin`')]
class Admin extends User
{
    #[ORM\Column(length: 50,nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 12,nullable: true)]
    private ?string $number = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_create = null;


    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }


    public function getDateCreate(): ?\DateTimeInterface
    {
        return $this->date_create;
    }

    #[ORM\PrePersist]
    public function setDateCreate(): void
    {
        $this->date_create = new \DateTime();
    }

}
