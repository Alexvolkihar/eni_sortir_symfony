<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Place;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventOutCityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('postalCode', TextType::class, [
                    'attr' => [
                        'readonly' => true,
                    ],
                ])
            ->add('street')
            ->add('city', EntityType::class,[
                'class' => City::class,
                'choice_label' => 'name',
                'attr' => [
                    'onchange' => '
                        let cityInput = document.getElementById("event_out_city_postalCode");
                        let cityId = this.value; // ID de la ville sélectionnée
                        let cityStreet = document.getElementById("event_out_city_street");
                        
                        // Effectuer une requête AJAX pour récupérer le code postal
                        let xhr = new XMLHttpRequest();
                        xhr.open("GET", "/getPostalCode/" + cityId, true);
                        xhr.onreadystatechange = function () {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                let response = JSON.parse(xhr.responseText);
                                let codePostal = response.postalCode;
                                cityInput.value = codePostal;
                                let street = response.street;
                                cityStreet.value = street;
                                console.log(cityInput.value);
                            }
                        };
                        xhr.send();
                   ']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Place::class,
        ]);
    }
}
