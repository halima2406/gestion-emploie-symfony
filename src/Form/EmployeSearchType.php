<?php

namespace App\Form;

use App\DTO\EmployeSearchFormDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Departements;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;







class EmployeSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
           

            ->add('numero', TextType::class, [
                'label'    => 'numéro',
                'attr' => [
                    'placeholder' => 'Rechercher un numero…',
                    'class' => 'filter-input',
                    'autocomplete'       => 'off',
                ],
                 
            ])
         
            
        
            ->add('departement', EntityType::class, [
                'class' => Departements::class,
                'choice_label' => 'nom',
                
                'placeholder' => 'Rechercher un département',
                'attr' => ['class' => 'filter-input'],
            ])
            
           


            ->add('statut', ChoiceType::class, [
                'label'       => 'Statut',
                'required'    => false,
                'placeholder' => '-- Statut --',
                'choices'     => [
                    'Actif'   => 'actif',
                    'Archivé' => 'archive',
                ],
                'expanded'    => false,   // <- select (plus des radios)
                'multiple'    => false,
                'attr'        => ['class' => 'filter-input'], // garde ton style
                'choice_translation_domain' => false,
            ])

            /*->add('btnSaveDept',SubmitType::class,[
                'label'      => 'Enregistrer',
             

            ])*/
        
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => EmployeSearchFormDto::class,
            'method'          => 'GET',   // <-- conserve les filtres dans l’URL
            'csrf_protection' => false, 
            'attr'=> [
                    'data-turbo' => false,
                ],
        ]);
    }
}
