<?php

namespace App\Controller;

use App\Form\Type\ClientType;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $client = $form->getData();
            $this->clientRepository->save($client, true);
            
            return $this->redirectToRoute('client_view', [
                'id' => $id,
            ]);
        }

        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }
}
