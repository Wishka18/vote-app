<?php

namespace App\DataFixtures;

use App\Entity\Candidate;
use App\Enum\Position;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CandidateFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach (Position::cases() as $position) {
            for ($i = 1; $i <= 3; $i++) {
                $candidate = new Candidate();
                $candidate->setName("Candidat $i - " . $position->value);
                $candidate->setPosition($position);
                $candidate->setDescription("Ceci est une description pour le poste de " . $position->value);

                // Alternate photo: user1.png for odd, user2.png for even
                $photo = ($i % 2 === 1) ? 'user1.png' : 'user2.png';
                $candidate->setPhoto($photo);

                $manager->persist($candidate);
            }
        }

        $manager->flush();
    }
}
