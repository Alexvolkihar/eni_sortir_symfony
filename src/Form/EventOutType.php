<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Site;
use App\Entity\State;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventOutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('startDateTime', DateType::class, [
                'html5' => true,
                'widget' => "single_text",
            ])
            ->add('subDateLimit', DateType::class, [
                'html5' => true,
                'widget' => "single_text",
            ])
            ->add('duration', TimeType::class)
            ->add('nbMaxSub', NumberType::class, [
                'html5' => true,
            ])
            ->add('eventInfo', TextType::class, [
                'label' => "Description des infos"
            ])
            ->add('place', EntityType::class, [
                'class' => Place::class,
                'choice_label' => 'name'
            ])
            ->add('state', EntityType::class, [
                'class' => State::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('u');
                },
                'choice_label' => 'label',
                'attr' => [
                    'class' => 'hidden',
                ],
                'label' => false,
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'name'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
