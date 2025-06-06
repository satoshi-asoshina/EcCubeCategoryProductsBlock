<?php

namespace Plugin\MakerProductBlock;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {
        return [
            'content' => [
                'children' => [
                    'maker_product_block' => [
                        'name' => 'メーカー商品ブロック',
                        'url' => 'maker_product_block_admin_index',
                    ],
                ],
            ],
        ];
    }
}
