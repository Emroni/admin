<?php

namespace App\Controller;

use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Repository\TimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /** @var InvoiceRepository */
    private $invoiceRepository;

    /** @var TimeRepository */
    private $timeRepository;

    public function __construct(InvoiceRepository $invoiceRepository, TimeRepository $timeRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->timeRepository = $timeRepository;
    }

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        // Get awaiting invoices
        $awaitingInvoices = $this->invoiceRepository->findAwaiting();

        // Get billable invoices
        $billableInvoices = [];
        foreach ($this->timeRepository->findBillable() as $time) {
            $client = $time->getTask()->getProject()->getClient();
            $clientId = $client->getId();

            if (!isset($billableInvoices[$clientId])) {
                $invoice = new Invoice();
                $invoice->setClient($client);
                $invoice->updateType();
                $billableInvoices[$clientId] = $invoice;
            }

            $invoice = $billableInvoices[$clientId];
            $invoice->addTime($time);
            $invoice->updateAmount();
        }

        return $this->render('dashboard/index.html.twig', [
            'awaitingInvoices' => $awaitingInvoices,
            'billableInvoices' => $billableInvoices,
        ]);
    }
}
