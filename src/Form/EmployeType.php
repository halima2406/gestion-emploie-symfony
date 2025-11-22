<?php

namespace App\Form;

use App\Entity\Departements;
use App\Entity\Employe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmployeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // App/Form/EmployeType.php
            ->add('codeAffiche', TextType::class, [
                'label'    => 'numéro',
                'mapped'   => false,     // <- ne touche pas l’entity
                'required' => false,
                'disabled' => true,      // <- lecture seule
                'attr'     => [
                    'placeholder' => 'Sera généré après enregistrement',
                    'class'       => 'form-control',
                ],
            ])
            ->add('nomComplet',null,[
                'required'   => false,
            ])
            ->add('telephone',null,[
                'required'   => false,
            ])
            


            ->add('embaucheAt', null, [
                'widget' => 'single_text',
                'input'  => 'datetime_immutable',
                'invalid_message' => 'Date invalide.',
              ])
           
            
            ->add('departement', EntityType::class, [
                'class' => Departements::class,
                'choice_label' => 'nom',
            ])
            
            ->add('isArchived',ChoiceType::class,[
                'label'=>"Archiver",
                "choices"=>[
                    'Non' => false,
                    'Oui' => true
                ],
                "expanded"=>true,

            ])
            ->add('btnSaveDept',SubmitType::class,[
                'label'      => 'Enregistrer',
            ])


            ->add('adresse', TextareaType::class, [
                'required'   => false,          // <— ICI (pas "require")
                'label'      => 'Adresse',
                'attr'       => [
                    'rows'        => 3,
                ],
            ])
            ->add('pays', TextType::class, [
                'mapped'   => false,
                'required' => false,
                'label'    => 'Pays',
                'attr'     => ['placeholder' => 'Ex : Sénégal'],
            ])
            ->add('ville', TextType::class, [
                'mapped'   => false,
                'required' => false,
                'label'    => 'Ville',
                'attr'     => ['placeholder' => 'Ex : Dakar'],
            ])
            ->add('rue', TextType::class, [
                'mapped'   => false,
                'required' => false,
                'label'    => 'Rue',
                'attr'     => ['placeholder' => 'Ex : Nord Foire …'],
            ])

            /*->add('photoFile', FileType::class, [
                'required'   => false,          // <— ICI (pas "require")
                'mapped'      => false,
                'label'      => 'Photo profil',
                'attr'       => [
                    'accept'        => "image/jpeg, image/png",
                ],
               
            ])*/

            ->add('photoFile', FileType::class, [
                'mapped'      => false,          // champ non stocké directement dans l’entity
                'required'    => true,           // obligatoire
                'constraints' => [
                    new NotBlank(['message' => "La photo de l'employé est obligatoire"]),
                    new Image([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg','image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG).',
                    ]),
                ],
            ]);

            
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employe::class,
            'attr'=> [
                    'data-turbo' => false,
                ],

        ]);
    }
}
