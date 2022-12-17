<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Form\Type\InvoiceType;
use App\Repository\ClientRepository;
use App\Repository\InvoiceRepository;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\TimeRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{

    /** @var ClientRepository */
    private $clientRepository;

    /** @var InvoiceRepository */
    private $invoiceRepository;
    
    /** @var ProjectRepository */
    private $projectRepository;
    
    /** @var TaskRepository */
    private $taskRepository;
    
    /** @var TimeRepository */
    private $timeRepository;

    public function __construct(ClientRepository $clientRepository, InvoiceRepository $invoiceRepository, ProjectRepository $projectRepository, TaskRepository $taskRepository, TimeRepository $timeRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->projectRepository = $projectRepository;
        $this->taskRepository = $taskRepository;
        $this->timeRepository = $timeRepository;
    }

    #[Route('/invoice', name: 'invoice_list')]
    public function list(): Response
    {
        $invoices = $this->invoiceRepository->findAll();

        return $this->render('invoice/list.html.twig', [
            'invoices' => $invoices,
        ]);
    }

    #[Route('/invoice/add', name: 'invoice_add')]
    public function add(Request $request): Response
    {
        // Create invoice
        $invoice = new Invoice();
        $invoice->setCurrency('EUR');
        $invoice->setSentDate(new DateTime());

        // Add times
        $client = null;
        $times = [];
        if ($request->get('client')) {
            $client = $this->clientRepository->find($request->get('client'));
            $times = $this->timeRepository->findBillableByClient($client);
            $invoice->setClient($client);
        } elseif ($request->get('project')) {
            $project = $this->projectRepository->find($request->get('project'));
            $client = $project->getClient();
            $invoice->setClient($client);
            $times = $this->timeRepository->findBillableByProject($project);
        } elseif ($request->get('task')) {
            $task = $this->taskRepository->find($request->get('task'));
            $client = $task->getProject()->getClient();
            $invoice->setClient($client);
            $times = $this->timeRepository->findBillableByTask($task);
        }
        foreach ($times as $time) {
            $invoice->addTime($time);
        }

        // Set amount and type
        $invoice->updateAmount();
        $invoice->updateType();

        // Create form
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            // Save invoice
            $invoice = $form->getData();
            $this->invoiceRepository->save($invoice, true);

            // Add notification
            $this->addFlash('success', "Added Invoice <b>{$invoice->getName()}</b>");
            
            // Redirect to invoice
            return $this->redirectToRoute('invoice_view', [
                'id' => $invoice->getId(),
            ]);
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Add Invoice',
        ]);
    }

    #[Route('/invoice/{id}', name: 'invoice_view')]
    public function view(int $id): Response
    {
        $invoice = $this->invoiceRepository->find($id);

        return $this->render('invoice/view.html.twig', [
            'invoice' => $invoice,
        ]);
    }

    #[Route('/invoice/{id}/edit', name: 'invoice_edit')]
    public function edit(int $id, Request $request): Response
    {
        $invoice = $this->invoiceRepository->find($id);

        // Create form
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        // Check submit
        if ($form->isSubmitted() && $form->isValid()) {
            $deleteButton = $form->get('delete');
            if ($deleteButton instanceof SubmitButton && $deleteButton->isClicked()) {
                // Delete invoice
                $this->invoiceRepository->remove($invoice, true);

                // Add notification
                $this->addFlash('danger', "Deleted Invoice <b>{$invoice->getName()}</b>");
                
                // Redirect to list
                return $this->redirectToRoute('invoice_list');

            } else {
                // Save invoice
                $invoice = $form->getData();
                $this->invoiceRepository->save($invoice, true);

                // Add notification
                $this->addFlash('success', "Updated Invoice <b>{$invoice->getName()}</b>");

                // Redirect to invoice
                return $this->redirectToRoute('invoice_view', [
                    'id' => $id,
                ]);
            }
        }

        return $this->render('partials/form.html.twig', [
            'form' => $form,
            'formTitle' => 'Edit ' . $invoice->getName(),
        ]);
    }

    #[Route('/invoice/{id}/paid', name: 'invoice_paid')]
    public function paid(int $id): Response
    {
        // Update invoice
        $invoice = $this->invoiceRepository->find($id);
        $invoice->setPaidDate(new DateTime());
        $this->invoiceRepository->save($invoice, true);

        // Add notification
        $this->addFlash('success', "Marked Invoice <b>{$invoice->getName()}</b> as paid");

        return $this->render('invoice/view.html.twig', [
            'invoice' => $invoice,
        ]);
    }
}
