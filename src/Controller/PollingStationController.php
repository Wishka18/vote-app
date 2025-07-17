<?php

namespace App\Controller;

use App\Entity\PollingStation;
use App\Form\PollingStationForm;
use App\Repository\PollingStationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/polling/station')]
final class PollingStationController extends AbstractController
{
    #[Route(name: 'app_polling_station_index', methods: ['GET'])]
    public function index(PollingStationRepository $pollingStationRepository): Response
    {
        return $this->render('polling_station/index.html.twig', [
            'polling_stations' => $pollingStationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_polling_station_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pollingStation = new PollingStation();
        $form = $this->createForm(PollingStationForm::class, $pollingStation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pollingStation);
            $entityManager->flush();

            return $this->redirectToRoute('app_polling_station_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('polling_station/new.html.twig', [
            'polling_station' => $pollingStation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_polling_station_show', methods: ['GET'])]
    public function show(PollingStation $pollingStation): Response
    {
        return $this->render('polling_station/show.html.twig', [
            'polling_station' => $pollingStation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_polling_station_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PollingStation $pollingStation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PollingStationForm::class, $pollingStation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_polling_station_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('polling_station/edit.html.twig', [
            'polling_station' => $pollingStation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_polling_station_delete', methods: ['POST'])]
    public function delete(Request $request, PollingStation $pollingStation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $pollingStation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pollingStation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_polling_station_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/toggle-open', name: 'app_polling_station_toggle_open', methods: ['POST'])]
    public function toggleOpen(PollingStation $pollingStation, EntityManagerInterface $em, Request $request): Response
    {
        $pollingStation->setIsOpen(!$pollingStation->isOpen());
        $em->flush();

        return $this->redirectToRoute('app_polling_station_index');
    }

    #[Route('/{id}/toggle-publish', name: 'app_polling_station_toggle_publish', methods: ['POST'])]
    public function togglePublish(PollingStation $pollingStation, EntityManagerInterface $em, Request $request): Response
    {
        $pollingStation->setIsResultPublished(!$pollingStation->isResultPublished());
        $em->flush();

        return $this->redirectToRoute('app_polling_station_index');
    }
}
