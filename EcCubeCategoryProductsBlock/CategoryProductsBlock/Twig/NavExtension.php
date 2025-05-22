<?php
// Plugin/CategoryProductsBlock/Twig/NavExtension.php

namespace Plugin\CategoryProductsBlock\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NavExtension extends AbstractExtension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('category_products_block_nav', [$this, 'getNav']),
        ];
    }

    /**
     * @return array
     */
    public function getNav()
    {
        return [
            'id' => 'category_products_block',
            'name' => 'カテゴリー商品ブロック',
            'icon' => 'fa-cubes',
            'children' => [
                [
                    'id' => 'category_products_block_config',
                    'name' => '設定',
                    'url' => 'category_products_block_admin_config',
                ],
            ],
        ];
    }
}