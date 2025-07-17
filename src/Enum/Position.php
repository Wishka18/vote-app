<?php

namespace App\Enum;

use Symfony\Component\String\Slugger\AsciiSlugger;

enum Position: string
{
    case PRESIDENT = 'Président';
    case VICE_PRESIDENT_1 = '1ᵉʳ Vice-Président';
    case VICE_PRESIDENT_2 = '2ᵉʳ Vice-Président';
    case SECRETARY_GENERAL = 'Secrétaire Général';
    case SECRETARY_GENERAL_ASSISTANT = 'Secrétaire Général Adjoint';
    case ORGANIZATION_SECRETARY = 'Secrétaire à l’Organisation';
    case ORGANIZATION_SECRETARY_ASSISTANT = 'Secrétaire à l’Organisation adjoint';
    case FINANCE_SECRETARY = 'Secrétaire chargé à l’économie et aux finances';
    case FINANCE_SECRETARY_ASSISTANT = 'Secrétaire chargé à l’économie et aux finances adjoint';
    case COMMUNICATION_SECRETARY = 'Secrétaire à l’information et à la communication';
    case COMMUNICATION_SECRETARY_ASSISTANT = 'Secrétaire à l’information et à la communication adjoint';
    case TREASURER_GENERAL = 'Trésorier Général';
    case TREASURER_GENERAL_ASSISTANT = 'Trésorier général Adjoint';
    case AUDITOR = 'Commissaire aux comptes et aux affaires sociales';
    case AUDITOR_ASSISTANT = 'Commissaire aux comptes et aux affaires sociales adjoint';

    public function getSlug(): string
    {
        $slugger = new AsciiSlugger();
        return $slugger->slug($this->value)->lower();
    }
}
