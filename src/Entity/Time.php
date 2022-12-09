<?php

namespace App\Entity;

use App\Repository\TimeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TimeRepository::class)]
class Time
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'times')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Task $task = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $duration = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDeletable(): bool
    {
        return true;
    }

    public function getName(): string
    {
        $date = $this->getDate();
        $duration = $this->getDuration();
        
        if ($date && $duration) {
            return $date->format('Y-m-d') . ' — ' . $duration->format('H:i');
        }

        return '0000-00-00 — 00:00';
    }

    public function getFullName(): ?string
    {
        $taskFullName = $this->getTask()->getFullName();

        return "{$taskFullName} › {$this->name}";
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;

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

    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration(\DateTimeInterface $duration): self
    {
        $this->duration = $duration;

        return $this;
    }
}
