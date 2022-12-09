<?php

namespace App\Controller;

use App\Entity\Time;
use App\Form\Type\TimeType;
use App\Repository\TaskRepository;
use App\Repository\TimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TimeController extends AbstractController
{
    /** @var TaskRepository */
    private $taskRepository;

    /** @var TimeRepository */
    private $timeRepository;

    public function __construct(TaskRepository $taskRepository, TimeRepository $timeRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->timeRepository = $timeRepository;
    }

    #[Route('/time', name: 'time_list')]
    public function list(): Response
    {
        $times = $this->timeRepository->findAll();

        return $this->render('time/list.html.twig', [
            'times' => $times,
        ]);
    }

    #[Route('/time/add', name: 'time_add')]
    public function add(Request $request): Response
    {
        // Create time
        $time = new Time();

        // Add task
        if ($request->get('task')) {
            $task = $this->taskRepository->find($request->get('task'));
            $time->setTask($task);
        }

        // Create form
        $form = $this->createForm(TimeType::class, $time);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            // Save time
            $time = $form->getData();
            $this->timeRepository->save($time, true);

            // Add notification
            $this->addFlash('success', "Added Time <b>{$time->getName()}</b>");
            
            // Redirect to time
            return $this->redirectToRoute('time_view', [
                'id' => $time->getId(),
            ]);
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Add Time',
        ]);
    }

    #[Route('/time/{id}', name: 'time_view')]
    public function view(int $id): Response
    {
        $time = $this->timeRepository->find($id);

        return $this->render('time/view.html.twig', [
            'time' => $time,
        ]);
    }

    #[Route('/time/{id}/edit', name: 'time_edit')]
    public function edit(int $id, Request $request): Response
    {
        $time = $this->timeRepository->find($id);

        // Create form
        $form = $this->createForm(TimeType::class, $time);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            $deleteButton = $form->get('delete');
            if ($deleteButton instanceof SubmitButton && $deleteButton->isClicked()) {
                // Delete time
                $this->timeRepository->remove($time, true);

                // Add notification
                $this->addFlash('danger', "Deleted Time <b>{$time->getName()}</b>");
                
                // Redirect to list
                return $this->redirectToRoute('time_list');

            } else {
                // Save time
                $time = $form->getData();
                $this->timeRepository->save($time, true);

                // Add notification
                $this->addFlash('success', "Updated Time <b>{$time->getName()}</b>");

                // Redirect to time
                return $this->redirectToRoute('time_view', [
                    'id' => $id,
                ]);
            }
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Edit ' . $time->getName(),
        ]);
    }
}
