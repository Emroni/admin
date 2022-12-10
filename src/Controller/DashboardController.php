<?php

namespace App\Controller;

use App\Repository\InvoiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /** @var InvoiceRepository */
    private $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        $awaitingInvoices = $this->invoiceRepository->findAwaiting();

        return $this->render('dashboard/index.html.twig', [
            'awaitingInvoices' => $awaitingInvoices,
        ]);
    }
}
