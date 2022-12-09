<?php

namespace App\Form\Type;

use App\Entity\Project;
use App\Entity\Task;
use App\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Build form
        $builder
            ->add('name', TextType::class)
            ->add('project', EntityType::class, [
                'class'         => Project::class,
                'choice_label' => 'name',
                'query_builder' => function (ProjectRepository $projectRepository) {
                    return $projectRepository->queryAll();
                },
            ])
            ->add('billing', ChoiceType::class, [
                'choices' => [
                    'Fixed' => 'fixed',
                    'Hourly' => 'hourly',
                ],
                'data' => 'hourly',
            ])
            ->add('currency', ChoiceType::class, [
                'choices' => [
                    'EUR' => 'EUR',
                    'USD' => 'USD',
                ],
                'data' => 'EUR',
            ])
            ->add('price', NumberType::class)
            ->add('save', SubmitType::class);

        // Add delete
        $task = $options['data'] ?? null;
        if ($task) {
            $builder->add('delete', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-danger',
                    'data-confirm' => 'Are you sure you want to delete this Task?',
                    'disabled' => !$task->isDeletable(),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
