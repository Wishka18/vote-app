<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?bool $duesPaid = null;

    #[ORM\Column(length: 6, unique: true)]
    private ?string $votingCode = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'member')]
    private Collection $votes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(nullable: true)]
    private ?int $promotionYear = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private array $annualPayments = [];





    public function __construct()
    {
        $this->votes = new ArrayCollection();
        // Generate a random 6-character voting code
        $this->votingCode = substr(strtoupper(bin2hex(random_bytes(3))), 0, 6);
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isDuesPaid(): ?bool
    {
        return $this->duesPaid;
    }

    public function setDuesPaid(bool $duesPaid): static
    {
        $this->duesPaid = $duesPaid;

        return $this;
    }

    public function getVotingCode(): ?string
    {
        return $this->votingCode;
    }

    public function setVotingCode(string $votingCode): static
    {
        $this->votingCode = $votingCode;

        return $this;
    }


    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setMember($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getMember() === $this) {
                $vote->setMember(null);
            }
        }

        return $this;
    }

    public function getVotingProgress(EntityManagerInterface $em): string
    {
        $totalPositions = count(\App\Enum\Position::cases()); // total positions available

        // Count distinct positions member voted for
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(DISTINCT c.position)')
            ->from(\App\Entity\Vote::class, 'v')
            ->join('v.candidate', 'c')
            ->where('v.member = :member')
            ->setParameter('member', $this);

        $count = (int)$qb->getQuery()->getSingleScalarResult();

        return "$count / $totalPositions";
    }

    public function hasVotedForAllPositions(EntityManagerInterface $em): bool
    {
        $totalPositions = count(\App\Enum\Position::cases());

        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(DISTINCT c.position)')
            ->from(\App\Entity\Vote::class, 'v')
            ->join('v.candidate', 'c')
            ->where('v.member = :member')
            ->setParameter('member', $this);

        $count = (int)$qb->getQuery()->getSingleScalarResult();

        return $count >= $totalPositions;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPromotionYear(): ?int
    {
        return $this->promotionYear;
    }

    public function setPromotionYear(?int $promotionYear): static
    {
        $this->promotionYear = $promotionYear;

        return $this;
    }

    public function getAnnualPayments(): ?array
    {
        return $this->annualPayments;
    }

    public function setAnnualPayments(?array $annualPayments): static
    {
        $this->annualPayments = $annualPayments;

        return $this;
    }

    public function getPaymentForYear(int $year): float
    {
        return $this->annualPayments[$year] ?? 0;
    }

    public function setPaymentForYear(int $year, float $amount): self
    {
        $this->annualPayments[$year] = $amount;
        return $this;
    }

    public function isEligibleToVote(array $requiredPayments): bool
    {
        foreach ($requiredPayments as $year => $requiredAmount) {
            if (($this->annualPayments[$year] ?? 0) < $requiredAmount) {
                return false;
            }
        }
        return true;
    }


    public function updateEligibility(array $requiredPayments): void
    {
        $this->duesPaid = $this->isEligibleToVote($requiredPayments);
    }
}
