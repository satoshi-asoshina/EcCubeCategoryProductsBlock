<?php

namespace Plugin\CategoryProductsBlock\Repository;

use Doctrine\ORM\EntityRepository;
use Plugin\CategoryProductsBlock\Entity\Config;

class ConfigRepository extends EntityRepository
{
    /**
     * 設定を取得する.
     *
     * @return Config|null
     */
    public function get()
    {
        return $this->findOneBy([]);
    }
}