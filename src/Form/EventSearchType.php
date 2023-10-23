<?php

namespace App\Form;

use App\Entity\Site;
use App\Data\SearchEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('site', EntityType::class, [
                'label' => 'Site :',
                'class' => Site::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez un site',
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Le nom de la sortie contient :'
            ])
            ->add('betweenFirstDate', DateType::class, [
                'html5' => true,
                'widget' => "single_text",
                'required' => false,
                'label' => 'Entre',
            ])
            ->add('betweenLastDate', DateType::class, [
                'html5' => true,
                'widget' => "single_text",
                'required' => false,
                'label' => 'et',
            ])
            ->add('isHost', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false
            ])
            ->add('isMember', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false
            ])
            ->add('notMember', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false
            ])
            ->add('passed', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SearchEvent::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
