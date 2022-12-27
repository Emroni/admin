<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Repository\TaskRepository;
use App\Repository\TimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /** @var InvoiceRepository */
    private $invoiceRepository;

    /** @var TaskRepository */
    private $taskRepository;

    /** @var TimeRepository */
    private $timeRepository;

    public function __construct(InvoiceRepository $invoiceRepository, TaskRepository $taskRepository, TimeRepository $timeRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->taskRepository = $taskRepository;
        $this->timeRepository = $timeRepository;
    }

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'awaitingInvoices' => $this->invoiceRepository->findAwaiting(),
            'billableInvoices' => $this->getBillableInvoices(),
            'timer' => $this->taskRepository->findOneWithTimer(),
        ]);
    }
    private function getBillableInvoices()
    {
        $billableInvoices = [];
        foreach ($this->timeRepository->findBillable() as $time) {
            $client = $time->getTask()->getProject()->getClient();
            $clientId = $client->getId();

            if (!isset($billableInvoices[$clientId])) {
                $invoice = new Invoice();
                $invoice->setClient($client);
                $invoice->setCurrency('EUR');
                $invoice->updateType();
                $billableInvoices[$clientId] = $invoice;
            }

            $invoice = $billableInvoices[$clientId];
            $invoice->addTime($time);
            $invoice->updateAmount();
        }
        return $billableInvoices;
    }
}
