<?php

namespace App\Form\Type;

use App\Entity\Task;
use App\Entity\Time;
use App\Repository\TaskRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType as SymfonyTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Get entities
        $time = $options['data'] ?? null;
        $task = $time->getTask();

        // Build form
        $builder
            ->add('task', EntityType::class, [
                'class'         => Task::class,
                'choice_label' => 'fullName',
                'query_builder' => function (TaskRepository $taskRepository) use ($task) {
                    if ($task) {
                        $project = $task->getProject();
                        return $taskRepository->queryByProject($project);
                    }
                    return $taskRepository->queryAll();
                },
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('duration', SymfonyTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('save', SubmitType::class);

        // Add delete
        if ($time) {
            $builder->add('delete', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-danger',
                    'data-confirm' => 'Are you sure you want to delete this Time?',
                    'disabled' => !$time->isDeletable(),
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Time::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_time_type';
    }
}
