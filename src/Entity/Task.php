<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $billing = null;

    #[ORM\Column(length: 255)]
    private ?string $currency = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Time::class)]
    private Collection $times;

    public function __construct()
    {
        $this->times = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDeletable(): ?bool
    {
        return !$this->getTimes()->count();
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

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

    public function getFullName(): ?string
    {
        return "{$this->getProject()->getFullName()} ‣ {$this->getName()}";
    }

    public function getBilling(): ?string
    {
        return $this->billing;
    }

    public function setBilling(string $billing): self
    {
        $this->billing = $billing;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Time>
     */
    public function getTimes(): Collection
    {
        return $this->times;
    }

    public function addTime(Time $time): self
    {
        if (!$this->times->contains($time)) {
            $this->times->add($time);
            $time->setTask($this);
        }

        return $this;
    }

    public function removeTime(Time $time): self
    {
        if ($this->times->removeElement($time) && $time->getTask() === $this) {
            $time->setTask(null);
        }

        return $this;
    }
}
