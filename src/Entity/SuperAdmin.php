<?php

namespace App\Entity;

use App\Repository\SuperAdminRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SuperAdminRepository::class)]
class SuperAdmin extends User
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_create = null;


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
