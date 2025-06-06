<?php

namespace Plugin\MakerProductBlock\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * メーカーブロック検索フォーム
 */
class SearchConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('maker_name', TextType::class, [
                'required' => false,
                'label' => 'メーカー名',
                'attr' => [
                    'placeholder' => 'メーカー名を入力',
                ],
            ])
            ->add('block_name', TextType::class, [
                'required' => false,
                'label' => 'ブロック名',
                'attr' => [
                    'placeholder' => 'ブロック名を入力',
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => '表示設定',
                'required' => false,
                'choices' => [
                    '全て' => '',
                    '表示する' => 1,
                    '表示しない' => 0,
                ],
            ]);
    }
}