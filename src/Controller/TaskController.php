<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\Type\TaskType;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    /** @var ProjectRepository */
    private $projectRepository;

    /** @var TaskRepository */
    private $taskRepository;

    public function __construct(ProjectRepository $projectRepository, TaskRepository $taskRepository)
    {
        $this->projectRepository = $projectRepository;
        $this->taskRepository = $taskRepository;
    }

    #[Route('/task', name: 'task_list')]
    public function list(): Response
    {
        $tasks = $this->taskRepository->findAll();

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/task/add', name: 'task_add')]
    public function add(Request $request): Response
    {
        // Create task
        $task = new Task();
        $task->setBilling('hourly');
        $task->setCurrency('EUR');

        // Add project
        if ($request->get('project')) {
            $project = $this->projectRepository->find($request->get('project'));
            $task->setProject($project);
        }

        // Create form
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            // Save task
            $task = $form->getData();
            $this->taskRepository->save($task, true);

            // Add notification
            $this->addFlash('success', "Added Task <b>{$task->getName()}</b>");
            
            // Redirect to task
            return $this->redirectToRoute('task_view', [
                'id' => $task->getId(),
            ]);
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Add Task',
        ]);
    }

    #[Route('/task/{id}', name: 'task_view')]
    public function view(int $id): Response
    {
        $task = $this->taskRepository->find($id);

        return $this->render('task/view.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/task/{id}/edit', name: 'task_edit')]
    public function edit(int $id, Request $request): Response
    {
        $task = $this->taskRepository->find($id);

        // Create form
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            $deleteButton = $form->get('delete');
            if ($deleteButton instanceof SubmitButton && $deleteButton->isClicked()) {
                // Delete task
                $this->taskRepository->remove($task, true);

                // Add notification
                $this->addFlash('danger', "Deleted Task <b>{$task->getName()}</b>");
                
                // Redirect to list
                return $this->redirectToRoute('task_list');

            } else {
                // Save task
                $task = $form->getData();
                $this->taskRepository->save($task, true);

                // Add notification
                $this->addFlash('success', "Updated Task <b>{$task->getName()}</b>");

                // Redirect to task
                return $this->redirectToRoute('task_view', [
                    'id' => $id,
                ]);
            }
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Edit ' . $task->getName(),
        ]);
    }
}
