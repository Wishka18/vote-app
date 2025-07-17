<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Entity\Member;
use App\Entity\Candidate;
use App\Entity\PollingStation;
use App\Enum\Position;
use App\Form\VoteForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Route('/vote')]
class VoteController extends AbstractController
{
    #[Route('/', name: 'vote_home')]
    public function index(EntityManagerInterface $em, SessionInterface $session): Response
    {
        $code = $session->get('votingCode');

        if (!$code) {
            return $this->redirectToRoute('vote_code_entry');
        }

        $member = $em->getRepository(Member::class)->findOneBy(['votingCode' => $code]);

        if (!$member) {
            $session->remove('votingCode');
            $this->addFlash('error', 'Code de vote invalide.');
            return $this->redirectToRoute('vote_code_entry');
        }

        if (!$member->isDuesPaid()) {
            $this->addFlash('error', "$member->getname(). Vous ne pouvez pas voter, veuillez vous mettre à jour sur vos cotisations.");
            $session->remove('votingCode');
            return $this->redirectToRoute('vote_code_entry');
        }

        $stationRepo = $em->getRepository(PollingStation::class);
        $stationStats = $stationRepo->getStationsWithStats();

        $votedPositions = array_map(
            fn($vote) => $vote->getCandidate()->getPosition(),
            $em->getRepository(Vote::class)->findBy(['member' => $member])
        );

        $votingProgress = $member->getVotingProgress($em);

        return $this->render('vote/index.html.twig', [
            'stationStats' => $stationStats,
            'votedPositions' => $votedPositions,
            'code' => $code,
            'member' => $member,
            'votingProgress' => $votingProgress,
        ]);
    }


    #[Route('/code', name: 'vote_code_entry')]
    public function enterCode(Request $request, SessionInterface $session, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $code = trim($request->request->get('votingCode'));

            $member = $em->getRepository(Member::class)->findOneBy(['votingCode' => $code]);

            if (!$member) {
                $this->addFlash('error', 'Code de vote invalide.');
                return $this->redirectToRoute('vote_code_entry');
            }

            // 🔥 Check dues here
            if (!$member->isDuesPaid()) {
                $this->addFlash('error', 'Vous ne pouvez pas voter, veuillez vous mettre à jour sur vos cotisations.');
                return $this->redirectToRoute('vote_code_entry');
            }

            // Store code in session
            $session->set('votingCode', $code);

            return $this->redirectToRoute('vote_home');
        }

        return $this->render('vote/code.html.twig');
    }


    #[Route('/{position}/{code}', name: 'vote_position')]
    public function voteForPosition(string $position, string $code, Request $request, EntityManagerInterface $em): Response
    {
        // Convert the position string (backing value) to enum instance
        try {
            $positionEnum = Position::from($position);
        } catch (\ValueError $e) {
            $this->addFlash('error', 'Position invalide.');
            return $this->redirectToRoute('vote_home');
        }

        $member = $em->getRepository(Member::class)->findOneBy(['votingCode' => $code]);
        if (!$member) {
            $this->addFlash('error', 'Code de vote invalide.');
            return $this->redirectToRoute('vote_code_entry');
        }

        // Check if polling station is open
        $station = $em->getRepository(PollingStation::class)->findOneBy(['position' => $positionEnum]);
        if (!$station || !$station->isOpen()) {
            $this->addFlash('error', 'Le bureau de vote pour ce poste est fermé.');
            return $this->redirectToRoute('vote_home');
        }

        // Check if member already voted for this position using QueryBuilder
        $existingVote = $em->getRepository(Vote::class)->findOneByMemberAndPosition($member, $positionEnum);


        if ($existingVote) {
            $this->addFlash('info', 'Vous avez déjà voté pour ce poste.');
            return $this->redirectToRoute('vote_home', ['code' => $code]);
        }

        // Get candidates for the position
        $candidates = $em->getRepository(Candidate::class)->findBy(['position' => $positionEnum]);

        if ($request->isMethod('POST')) {
            $candidateId = $request->request->get('candidate');
            $candidate = $em->getRepository(Candidate::class)->find($candidateId);

            // Validate candidate and position match
            if ($candidate && $candidate->getPosition() === $positionEnum) {
                $vote = new Vote();
                $vote->setMember($member);
                $vote->setCandidate($candidate);

                $em->persist($vote);
                $em->flush();

                $this->addFlash('success', 'Votre vote a été enregistré.');
            } else {
                $this->addFlash('error', 'Candidat invalide.');
            }

            return $this->redirectToRoute('vote_home', ['code' => $code]);
        }

        return $this->render('vote/position.html.twig', [
            'position' => $positionEnum,
            'candidates' => $candidates,
            'code' => $code,
        ]);
    }



    #[Route('/form/{code}', name: 'vote_form')]
    public function voteForm(string $code, Request $request, EntityManagerInterface $em): Response
    {
        $member = $em->getRepository(Member::class)->findOneBy(['votingCode' => $code]);

        if (!$member) {
            $this->addFlash('error', 'Code de vote invalide.');
            return $this->redirectToRoute('vote_code_entry');
        }

        if (!$member->isDuesPaid()) {
            $this->addFlash('error', 'Cotisation non réglée.');
            return $this->redirectToRoute('vote_code_entry');
        }

        if ($member->hasVotedForAllPositions($em)) {
            $this->addFlash('info', 'Merci, vous avez déjà voté pour tous les postes.');
            return $this->redirectToRoute('vote_code_entry');
        }

        $form = $this->createForm(VoteForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach (\App\Enum\Position::cases() as $position) {
                $candidate = $form->get($position->name)->getData();
                $vote = new \App\Entity\Vote();
                $vote->setMember($member);
                $vote->setCandidate($candidate);
                $em->persist($vote);
            }

            $member->setHasVoted(true);
            $em->flush();

            return $this->render('vote/confirmation.html.twig');
        }

        return $this->render('vote/form.html.twig', [
            'form' => $form->createView(),
            'member' => $member,
        ]);
    }

    #[Route('/results', name: 'vote_results')]
    public function results(EntityManagerInterface $em): Response
    {
        $slugger = new AsciiSlugger();

        $pollingStations = $em->getRepository(\App\Entity\PollingStation::class)->findAll();
        $user = $this->getUser(); // null if not logged in

        // Check if at least one result is published
        $hasPublishedResults = array_reduce(
            $pollingStations,
            fn($carry, $station) => $carry || $station->isResultPublished(),
            false
        );

        // If user is not admin and no results are published, show "coming soon" page
        if (!$hasPublishedResults && (!$user || !$this->isGranted('ROLE_ADMIN'))) {
            return $this->render('vote/coming_soon.html.twig');
        }

        // Build results array
        $positions = \App\Enum\Position::cases();
        $results = [];

        foreach ($positions as $position) {
            $slug = $slugger->slug($position->value)->lower();

            // Get all candidates for this position
            $candidates = $em->getRepository(\App\Entity\Candidate::class)->findBy([
                'position' => $position
            ]);

            $positionResults = [];

            foreach ($candidates as $candidate) {
                $voteCount = $em->getRepository(\App\Entity\Vote::class)->count([
                    'candidate' => $candidate
                ]);

                $positionResults[] = [
                    'candidate' => $candidate,
                    'votes' => $voteCount,
                ];
            }

            // Sort descending by vote count
            usort($positionResults, fn($a, $b) => $b['votes'] <=> $a['votes']);

            // Only include positions where isResultPublished is true (for non-admins)
            $station = $em->getRepository(\App\Entity\PollingStation::class)->findOneBy(['position' => $position]);

            if ($user && $this->isGranted('ROLE_ADMIN')) {
                // Admin sees all
                $results[] = [
                    'position' => $position->value,
                    'slug' => $slug,
                    'candidates' => $positionResults,
                ];
            } elseif ($station && $station->isResultPublished()) {
                // Normal user only sees published
                $results[] = [
                    'position' => $position->value,
                    'slug' => $slug,
                    'candidates' => $positionResults,
                ];
            }
        }

        return $this->render('vote/results.html.twig', [
            'results' => $results,
        ]);
    }




    #[Route('/logout', name: 'vote_logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->remove('votingCode');  // Clear the stored voting code
        // Or $session->clear(); to clear all session data if you want

        return $this->redirectToRoute('vote_code_entry'); // Redirect to code entry page
    }
}
