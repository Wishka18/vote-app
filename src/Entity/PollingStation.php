<?php

namespace App\Entity;

use App\Enum\Position;
use App\Repository\PollingStationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ORM\Entity(repositoryClass: PollingStationRepository::class)]
class PollingStation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: Position::class)]
    private ?Position $position = null;

    #[ORM\Column]
    private ?bool $isOpen = false;

    #[ORM\Column]
    private ?bool $isResultPublished = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function isOpen(): ?bool
    {
        return $this->isOpen;
    }

    public function setIsOpen(bool $isOpen): static
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    public function isResultPublished(): ?bool
    {
        return $this->isResultPublished;
    }

    public function setIsResultPublished(bool $isResultPublished): static
    {
        $this->isResultPublished = $isResultPublished;

        return $this;
    }

    public function getPositionSlug(): string
    {
        $slugger = new AsciiSlugger();
        return $slugger->slug($this->getPosition()->value)->lower();
    }
}
