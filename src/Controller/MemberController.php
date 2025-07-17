<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberForm;
use App\Repository\MemberRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/member')]
final class MemberController extends AbstractController
{
    #[Route(name: 'app_member_index', methods: ['GET'])]
    public function index(MemberRepository $memberRepository): Response
    {
        return $this->render('member/index.html.twig', [
            'members' => $memberRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_member_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberForm::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($member);
            $entityManager->flush();

            return $this->redirectToRoute('app_member_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('member/new.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }
    /*Cotisation management page */

    #[Route('/payments', name: 'member_payments')]
    public function payments(Request $request, MemberRepository $memberRepository): Response
    {
        // Define the years dynamically
        $years = [2023, 2024, 2025]; // Add 2026 later

        $members = $memberRepository->findAll();

        return $this->render('member/payments.html.twig', [
            'members' => $members,
            'years' => $years,
        ]);
    }


    #[Route('/update-dues-status', name: 'update_dues_status', methods: ['POST'])]
    public function updateDuesStatus(Request $request, MemberRepository $memberRepository, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Define required payments for eligibility
        $requiredPayments = [
            2024 => 30000,
            2025 => 10000,
            // Add 2026 later here when needed
        ];

        try {
            foreach ($data as $memberId => $years) {
                /** @var Member $member */
                $member = $memberRepository->find($memberId);
                if (!$member) {
                    continue;
                }

                // Update annual payments
                foreach ($years as $year => $amount) {
                    $member->setPaymentForYear((int)$year, (float)$amount);
                }

                // Update duesPaid based on the updated rules
                $member->updateEligibility($requiredPayments);
            }

            $em->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }




    #[Route('/generate-codes', name: 'app_member_generate_codes', methods: ['POST'])]
    public function generateCodesForAll(EntityManagerInterface $em, MemberRepository $memberRepository): JsonResponse
    {
        try {
            $members = $memberRepository->findAll();
            foreach ($members as $member) {
                // Generate random 6-character code
                $member->setVotingCode(substr(strtoupper(bin2hex(random_bytes(3))), 0, 6));
            }

            $em->flush();

            return new JsonResponse(['success' => true, 'message' => 'Codes générés avec succès.']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la génération des codes.']);
        }
    }


    #[Route('/generate-code', name: 'app_member_generate_code', methods: ['GET'])]
    public function generateCode(): JsonResponse
    {
        $code = strtoupper(bin2hex(random_bytes(3))); // Example: 6-char code
        return new JsonResponse(['code' => $code]);
    }

    #[Route('/{id}', name: 'app_member_show', methods: ['GET'])]
    public function show(Member $member): Response
    {
        return $this->render('member/show.html.twig', [
            'member' => $member,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_member_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Member $member, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MemberForm::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_member_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('member/edit.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_member_delete', methods: ['POST'])]
    public function delete(Request $request, Member $member, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $member->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($member);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_member_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle-dues', name: 'app_member_toggle_dues', methods: ['POST'])]
    public function toggleDues(Member $member, EntityManagerInterface $em): Response
    {
        $member->setDuesPaid(!$member->isDuesPaid());
        $em->flush();

        return $this->redirectToRoute('app_member_index');
    }
}
