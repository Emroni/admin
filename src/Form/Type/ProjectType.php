<?php

namespace App\Form\Type;

use App\Entity\Client;
use App\Entity\Project;
use App\Repository\ClientRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Build form
        $builder
            ->add('name', TextType::class)
            ->add('client', EntityType::class, [
                'class'         => Client::class,
                'choice_label' => 'name',
                'query_builder' => function (ClientRepository $clientRepository) {
                    // TODO: Replace with find
                    return $clientRepository->queryAll();
                },
            ])
            ->add('save', SubmitType::class);

        // Add delete
        $project = $options['data'] ?? null;
        if ($project) {
            $builder->add('delete', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-danger',
                    'data-confirm-text-value' => 'Are you sure you want to delete this Project?',
                    'disabled' => !$project->isDeletable(),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
