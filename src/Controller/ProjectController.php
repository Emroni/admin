<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\Type\ProjectType;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /** @var ClientRepository */
    private $clientRepository;

    /** @var ProjectRepository */
    private $projectRepository;

    public function __construct(ClientRepository $clientRepository, ProjectRepository $projectRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->projectRepository = $projectRepository;
    }

    #[Route('/project', name: 'project_list')]
    public function list(): Response
    {
        $projects = $this->projectRepository->findAll();

        return $this->render('project/list.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project/add', name: 'project_add')]
    public function add(Request $request): Response
    {
        // Create project
        $project = new Project();

        // Add client
        if ($request->get('client')) {
            $client = $this->clientRepository->find($request->get('client'));
            $project->setClient($client);
        }

        // Create form
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            // Save project
            $project = $form->getData();
            $this->projectRepository->save($project, true);

            // Add notification
            $this->addFlash('success', "Added Project <b>{$project->getName()}</b>");
            
            // Redirect to project
            return $this->redirectToRoute('project_view', [
                'id' => $project->getId(),
            ]);
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Add Project',
        ]);
    }

    #[Route('/project/{id}', name: 'project_view')]
    public function view(int $id): Response
    {
        $project = $this->projectRepository->find($id);

        return $this->render('project/view.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/project/{id}/edit', name: 'project_edit')]
    public function edit(int $id, Request $request): Response
    {
        $project = $this->projectRepository->find($id);

        // Create form
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            $deleteButton = $form->get('delete');
            if ($deleteButton instanceof SubmitButton && $deleteButton->isClicked()) {
                // Delete project
                $this->projectRepository->remove($project, true);

                // Add notification
                $this->addFlash('danger', "Deleted Project <b>{$project->getName()}</b>");
                
                // Redirect to list
                return $this->redirectToRoute('project_list');

            } else {
                // Save project
                $project = $form->getData();
                $this->projectRepository->save($project, true);

                // Add notification
                $this->addFlash('success', "Updated Project <b>{$project->getName()}</b>");

                // Redirect to project
                return $this->redirectToRoute('project_view', [
                    'id' => $id,
                ]);
            }
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Edit ' . $project->getName(),
        ]);
    }
}
