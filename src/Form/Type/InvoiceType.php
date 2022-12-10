<?php

namespace App\Form\Type;

use DateTime;
use App\Entity\Project;
use App\Entity\Invoice;
use App\Repository\ProjectRepository;
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Get entities
        $invoice = $options['data'] ?? null;
        $project = $invoice->getProject();

        // Build form
        $builder
            ->add('project', EntityType::class, [
                'class'         => Project::class,
                'choice_label' => 'fullName',
                'query_builder' => function (ProjectRepository $projectRepository) use ($project) {
                    if ($project) {
                        $client = $project->getClient();
                        return $projectRepository->queryByClient($client);
                    }
                    return $projectRepository->queryAll();
                },
            ])
            ->add('type', TextType::class)
            ->add('currency', ChoiceType::class, [
                'choices' => [
                    'EUR' => 'EUR',
                    'USD' => 'USD',
                ],
                'data' => 'EUR',
            ])
            ->add('amount', NumberType::class)
            ->add('sent_date', DateType::class, [
                'data' => new DateTime(),
                'widget' => 'single_text',
            ])
            ->add('paid_date', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
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
