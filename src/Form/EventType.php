<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Site;
use App\Entity\State;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Time;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('startDateTime', DateType::class, [
                'html5' => true,
                'widget' => "single_text"
            ])
            ->add('duration')
            ->add('subDateLimit', DateType::class, [
                'html5' => true,
                'widget' => "single_text"
            ])
            ->add('nbMaxSub')
            ->add('eventInfo')
            ->add('place')
            ->add('state', EntityType::class, [
                'class' => State::class,
                'choice_label' => 'name'
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez un site',
            ])
            ->add('host')
            ->add('members')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
