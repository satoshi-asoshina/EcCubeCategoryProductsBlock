<?php

namespace Plugin\MakerProductBlock\Form\Type\Admin;

use Plugin\MakerProductBlock\Entity\MakerBlock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * メーカーブロック設定フォーム
 */
class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('maker_id', EntityType::class, [
                'class' => 'Plugin\Maker42\Entity\Maker',
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'メーカー',
                'required' => true,
                'placeholder' => '選択してください',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('block_name', TextType::class, [
                'label' => 'ブロック名',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('product_count', IntegerType::class, [
                'label' => '表示商品数',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 1, 'max' => 20]),
                ],
                'attr' => [
                    'min' => 1,
                    'max' => 20,
                ],
            ])
            ->add('visible_count', IntegerType::class, [
                'label' => '一画面表示件数（PC）',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 1, 'max' => 6]),
                ],
                'attr' => [
                    'min' => 1,
                    'max' => 6,
                ],
            ])
            ->add('visible_count_sp', IntegerType::class, [
                'label' => '一画面表示件数（スマホ）',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 1, 'max' => 2]),
                ],
                'attr' => [
                    'min' => 1,
                    'max' => 2,
                ],
            ])
            ->add('sort_type', ChoiceType::class, [
                'label' => '商品ソート順',
                'choices' => [
                    '新着順' => 'new',
                    '価格順' => 'price',
                    '在庫数順' => 'stock',
                ],
                'required' => true,
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('is_enabled', ChoiceType::class, [
                'label' => '表示設定',
                'choices' => [
                    '表示する' => true,
                    '表示しない' => false,
                ],
                'required' => true,
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('sort_no', IntegerType::class, [
                'label' => '表示順',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'max' => 9999,
                ],
            ]);
            
        // FormイベントでMakerオブジェクトからIDを取得
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            
            // maker_idがMakerオブジェクトの場合、IDを設定
            if ($data->getMakerId() instanceof \Plugin\Maker42\Entity\Maker) {
                $data->setMakerId($data->getMakerId()->getId());
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MakerBlock::class,
        ]);
    }
}
