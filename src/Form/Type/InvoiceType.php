<?php

namespace App\Form\Type;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\Time;
use App\Repository\ClientRepository;
use App\Repository\TimeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceType extends AbstractType
{
    /** @var ClientRepository */
    private $clientRepository;

    /** @var TimeRepository */
    private $timeRepository;

    public function __construct(ClientRepository $clientRepository, TimeRepository $timeRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->timeRepository = $timeRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Get invoice
        $invoice = $options['data'] ?? null;

        // Get times
        $times = [];
        if ($invoice) {
            $times = $invoice->getTimes();
            if (!$invoice->getId() && $times[0]) {
                $client = $times[0]->getTask()->getProject()->getClient();
                $times = $this->timeRepository->findBillableByClient($client);
            }
        }

        // Build form
        $builder
            ->add('client', EntityType::class, [
                'choices' => $this->clientRepository->findAll(),
                'choice_label' => 'name',
                'class' => Client::class,
            ])
            ->add('type', TextType::class)
            ->add('currency', ChoiceType::class, [
                'choices' => [
                    'EUR' => 'EUR',
                    'USD' => 'USD',
                ],
            ])
            ->add('amount', NumberType::class)
            ->add('sent_date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('paid_date', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('times', EntityType::class, [
                'choice_label' => 'fullName',
                'choices' => $times,
                'class' => Time::class,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('save', SubmitType::class);

        // Add delete
        if ($invoice) {
            $builder->add('delete', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-danger',
                    'data-confirm' => 'Are you sure you want to delete this Invoice?',
                    'disabled' => !$invoice->isDeletable(),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Invoice::class,
        ]);
    }
}
