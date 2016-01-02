<?php
namespace QuoteDB\Form;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class QuoteType extends AbstractType
{
    /**
     * 
     * @var Application
     */
    private $app;
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quote', TextareaType::class)
            ->add('author', TextType::class, array(
                'attr' => array(
                    'class' => 'typeahead'
                )
            ));
        
        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'QuoteDB\Entity\Quote'
        ));
    }
}
