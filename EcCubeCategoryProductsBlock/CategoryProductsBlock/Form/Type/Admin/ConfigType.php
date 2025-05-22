<?php

namespace Plugin\CategoryProductsBlock\Form\Type\Admin;

use Symfony\Component\Form\AbstractType; 
use Eccube\Form\Type\Master\CategoryType;
use Plugin\CategoryProductsBlock\Entity\Config;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType; 
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('display_num', IntegerType::class, [
                'label' => '表示件数',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 50,
                    ]),
                ],
            ])
            ->add('row_num', IntegerType::class, [
                'label' => '行数',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 5,
                    ]),
                ],
            ])
            ->add('col_num', IntegerType::class, [
                'label' => '列数',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range([
                        'min' => 1,
                        'max' => 6,
                    ]),
                ],
            ])
            ->add('default_category', CategoryType::class, [
                'label' => 'デフォルトカテゴリー',
                'required' => false,
                'choice_label' => 'NameWithLevel',
            ])
            ->add('display_style', ChoiceType::class, [
                'label' => '表示スタイル',
                'choices' => [
                    'グリッド表示' => 'grid',
                    'リスト表示' => 'list',
                ],
                'expanded' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('show_on_top', CheckboxType::class, [
                'label' => 'トップページに自動表示',
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}