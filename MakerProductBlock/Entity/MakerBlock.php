<?php

namespace Plugin\MakerProductBlock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;

/**
 * @ORM\Table(name="plg_maker_product_block")
 * @ORM\Entity(repositoryClass="Plugin\MakerProductBlock\Repository\MakerBlockRepository")
 */
class MakerBlock extends AbstractEntity
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="maker_id", type="integer", options={"unsigned":true})
     */
    private $maker_id;

    /**
     * @var int
     * @ORM\Column(name="block_id", type="integer", options={"unsigned":true})
     */
    private $block_id;

    /**
     * @var string
     * @ORM\Column(name="block_name", type="string", length=255)
     */
    private $block_name;

    /**
     * @var int
     * @ORM\Column(name="product_count", type="integer", options={"default":5})
     */
    private $product_count = 5;

    /**
     * @var int
     * @ORM\Column(name="visible_count", type="integer", options={"default":4})
     */
    private $visible_count = 4;

    /**
     * @var int
     * @ORM\Column(name="visible_count_sp", type="integer", options={"default":1})
     */
    private $visible_count_sp = 1;

    /**
     * @var string
     * @ORM\Column(name="sort_type", type="string", length=20, options={"default":"new"})
     */
    private $sort_type = 'new';

    /**
     * @var bool
     * @ORM\Column(name="is_enabled", type="boolean", options={"default":true})
     */
    private $is_enabled = true;

    /**
     * @var \DateTime
     * @ORM\Column(name="create_date", type="datetimetz")
     */
    private $create_date;

    /**
     * @var \DateTime
     * @ORM\Column(name="update_date", type="datetimetz")
     */
    private $update_date;

    /**
     * @var int
     * @ORM\Column(name="sort_no", type="integer", options={"default":0})
     */
    private $sort_no = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getMakerId()
    {
        return $this->maker_id;
    }

    /**
     * @param int $maker_id
     * @return $this
     */
    public function setMakerId($maker_id)
    {
        $this->maker_id = $maker_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getBlockId()
    {
        return $this->block_id;
    }

    /**
     * @param int $block_id
     * @return $this
     */
    public function setBlockId($block_id)
    {
        $this->block_id = $block_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getBlockName()
    {
        return $this->block_name;
    }

    /**
     * @param string $block_name
     * @return $this
     */
    public function setBlockName($block_name)
    {
        $this->block_name = $block_name;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductCount()
    {
        return $this->product_count;
    }

    /**
     * @param int $product_count
     * @return $this
     */
    public function setProductCount($product_count)
    {
        $this->product_count = $product_count;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisibleCount()
    {
        return $this->visible_count;
    }

    /**
     * @param int $visible_count
     * @return $this
     */
    public function setVisibleCount($visible_count)
    {
        $this->visible_count = $visible_count;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisibleCountSp()
    {
        return $this->visible_count_sp;
    }

    /**
     * @param int $visible_count_sp
     * @return $this
     */
    public function setVisibleCountSp($visible_count_sp)
    {
        $this->visible_count_sp = $visible_count_sp;
        return $this;
    }

    /**
     * @return string
     */
    public function getSortType()
    {
        return $this->sort_type;
    }

    /**
     * @param string $sort_type
     * @return $this
     */
    public function setSortType($sort_type)
    {
        $this->sort_type = $sort_type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_enabled
     * @return $this
     */
    public function setEnabled($is_enabled)
    {
        $this->is_enabled = $is_enabled;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param \DateTime $create_date
     * @return $this
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * @param \DateTime $update_date
     * @return $this
     */
    public function setUpdateDate($update_date)
    {
        $this->update_date = $update_date;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortNo()
    {
        return $this->sort_no;
    }

    /**
     * @param int $sort_no
     * @return $this
     */
    public function setSortNo($sort_no)
    {
        $this->sort_no = $sort_no;
        return $this;
    }
}
