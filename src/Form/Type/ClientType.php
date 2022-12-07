<?php

namespace App\Form\Type;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Build form
        $builder
            ->add('name', TextType::class)
            ->add('email', EmailType::class, [
                'required' => false,
            ])
            ->add('address', TextareaType::class, [
                'attr' => [
                    'rows' => 3,
                ],
                'required' => false,
            ])
            ->add('save', SubmitType::class);

        // Add delete
        $client = $options['data'] ?? null;
        if ($client) {
            $builder->add('delete', SubmitType::class, [
                'attr' => [
                    'class' => 'btn-danger',
                    'disabled' => !$client->isDeletable(),
                    'onclick' => 'window.confirm(\'Are you sure?\') ? null : event.preventDefault();'
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }

}
