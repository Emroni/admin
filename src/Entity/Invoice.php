<?php

namespace App\Entity;

use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $number = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $sentDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $paidDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $currency = null;

    #[ORM\OneToMany(mappedBy: 'invoice', targetEntity: Time::class)]
    #[ORM\OrderBy(['date' => 'DESC'])]
    private Collection $times;

    #[ORM\ManyToOne(inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\OrderBy(['id' => 'DESC'])]
    private ?Client $client = null;

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
        return true;
    }

    public function getName(): string
    {
        $number = $this->getNumber();

        return 'Invoice ' . $this->getId() . ($number ? " - {$number}" : '');
    }

    public function getFullName(): ?string
    {
        return "{$this->getClient()->getName()} › {$this->getName()}";
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function updateAmount(): float
    {
        $times = $this->getTimes()->toArray();
        $amount = array_reduce($times, function ($carry, $time) {
            return $carry + $time->getPrice();
        }, 0);
        $this->setAmount($amount);
        return $amount;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function updateType(): ?string
    {
        $client = $this->getClient();
        if ($client) {
            $lastInvoice = $client->getInvoices()->first();
            if ($lastInvoice) {
                $type = $lastInvoice->getType();
                $this->setType($type);
                return $type;
            }
        }
        return null;
    }

    public function getSentDate(): ?\DateTimeInterface
    {
        return $this->sentDate;
    }

    public function setSentDate(\DateTimeInterface $sentDate): self
    {
        $this->sentDate = $sentDate;

        return $this;
    }

    public function getPaidDate(): ?\DateTimeInterface
    {
        return $this->paidDate;
    }

    public function setPaidDate(?\DateTimeInterface $paidDate): self
    {
        $this->paidDate = $paidDate;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

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
            $time->setInvoice($this);
        }

        return $this;
    }

    public function removeTime(Time $time): self
    {
        if ($this->times->removeElement($time) && $time->getInvoice() === $this) {
            $time->setInvoice(null);
        }

        return $this;
    }
    
    /**
     * @return ArrayCollection<int, Task>
     */
    public function getTasks(): ArrayCollection
    {
        // TODO: Can this be a query?
        
        $tasks = [];

        foreach ($this->getTimes() as $time) {
            $task = $time->getTask();
            $tasks[$task->getId()] = $task;
        }

        ksort($tasks);

        return new ArrayCollection($tasks);
    }
    
    /**
     * @return ArrayCollection<int, Project>
     */
    public function getProjects(): ArrayCollection
    {
        // TODO: Can this be a query?
        
        $projects = [];

        foreach ($this->getTasks() as $task) {
            $project = $task->getProject();
            $projects[$project->getId()] = $project;
        }

        ksort($projects);

        return new ArrayCollection($projects);
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
