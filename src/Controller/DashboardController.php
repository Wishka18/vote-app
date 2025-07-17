<?php

namespace App\Controller;

use App\Enum\Position;
use App\Repository\VoteRepository;
use App\Repository\MemberRepository;
use App\Repository\CandidateRepository;
use App\Repository\PollingStationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/')]
class DashboardController extends AbstractController
{

    #[Route('', name: 'app_home')]
    public function homeRedirect(): Response
    {
        return $this->redirectToRoute('vote_code_entry');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/dashboard', name: 'dashboard')]
    public function index(
        VoteRepository $voteRepo,
        MemberRepository $memberRepo,
        CandidateRepository $candidateRepo,
        PollingStationRepository $stationRepo
    ): Response {
        $totalVotes = $voteRepo->countAllVotes();
        $totalMembers = $memberRepo->countActiveMembers();
        $totalStations = $stationRepo->countAllStations();
        $openStations = $stationRepo->countOpenStations();

        $candidatesPerPosition = [];
        $liveLeaders = [];

        foreach (Position::cases() as $position) {
            $candidatesPerPosition[$position->value] = $candidateRepo->countCandidatesPerPosition($position);
            $liveLeaders[$position->value] = $voteRepo->getLiveLeaderForPosition($position);
        }

        return $this->render('dashboard/index.html.twig', [
            'total_votes' => $totalVotes,
            'total_members' => $totalMembers,
            'total_stations' => $totalStations,
            'open_stations' => $openStations,
            'candidates_per_position' => $candidatesPerPosition,
            'live_leaders' => $liveLeaders
        ]);
    }
}
