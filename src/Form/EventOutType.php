<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Event;
use App\Entity\Place;
use App\Entity\Site;
use App\Entity\State;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventOutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('startDateTime', DateType::class, [
                'required' => true])
            ->add('subDateLimit', DateType::class, [
                'required' => true])
            ->add('nbMaxSub', TextType::class, [
                'required' => true])
            ->add('duration',DateType::class)
            ->add('eventInfo', TextType::class, [
                'label' => "Description des infos",
                'required' => true])
            ->add('place',EntityType::class,[
                'class' => Place::class,
                'choice_label' => 'name'
            ])
            ->add('site', EntityType::class,[
                'class' => Site::class,
                'choice_label' => 'name'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
