<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Panier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email du client',
                'attr' => ['class' => 'form-control']
            ])
            ->add('date_commande', DateTimeType::class, [
                'label' => 'Date de commande',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Adresse de livraison',
                'attr' => ['class' => 'form-control']
            ])
            ->add('panier', EntityType::class, [
                'class' => Panier::class,
                'choice_label' => 'id',
                'label' => 'Panier',
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
} 