<?php

namespace App\Controller;

use App\Form\Type\ClientType;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    /** @var ClientRepository */
    private $clientRepository;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    #[Route('/client', name: 'client_list')]
    public function list(): Response
    {
        $clients = $this->clientRepository->findAll();

        return $this->render('client/list.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/client/add', name: 'client_add')]
    public function add(Request $request): Response
    {
        // Create form
        $form = $this->createForm(ClientType::class);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            // Save client
            $client = $form->getData();
            $this->clientRepository->save($client, true);

            // Add notification
            $this->addFlash('success', "Added Client <b>{$client->getName()}</b>");
            
            // Redirect to client
            return $this->redirectToRoute('client_view', [
                'id' => $client->getId(),
            ]);
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Add Client',
        ]);
    }

    #[Route('/client/{id}', name: 'client_view')]
    public function view(int $id): Response
    {
        $client = $this->clientRepository->find($id);

        return $this->render('client/view.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/client/{id}/edit', name: 'client_edit')]
    public function edit(int $id, Request $request): Response
    {
        $client = $this->clientRepository->find($id);

        // Create form
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            $deleteButton = $form->get('delete');
            if ($deleteButton instanceof SubmitButton && $deleteButton->isClicked()) {
                // Delete client
                $this->clientRepository->remove($client, true);

                // Add notification
                $this->addFlash('danger', "Deleted Client <b>{$client->getName()}</b>");
                
                // Redirect to list
                return $this->redirectToRoute('client_list');

            } else {
                // Save client
                $client = $form->getData();
                $this->clientRepository->save($client, true);

                // Add notification
                $this->addFlash('success', "Updated Client <b>{$client->getName()}</b>");

                // Redirect to client
                return $this->redirectToRoute('client_view', [
                    'id' => $id,
                ]);
            }
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Edit ' . $client->getName(),
        ]);
    }
}
