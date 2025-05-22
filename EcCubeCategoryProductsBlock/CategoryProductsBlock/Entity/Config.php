<?php

namespace Plugin\CategoryProductsBlock\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Category;

/**
 * @ORM\Table(name="plg_category_products_block_config")
 * @ORM\Entity(repositoryClass="Plugin\CategoryProductsBlock\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="display_num", type="integer", options={"unsigned":true})
     */
    private $display_num;

    /**
     * @var int
     *
     * @ORM\Column(name="row_num", type="integer", options={"unsigned":true})
     */
    private $row_num;

    /**
     * @var int
     *
     * @ORM\Column(name="col_num", type="integer", options={"unsigned":true})
     */
    private $col_num;

    /**
     * @var string
     *
     * @ORM\Column(name="display_style", type="string", length=20)
     */
    private $display_style = 'grid';

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="default_category_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $default_category;

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
    public function getDisplayNum()
    {
        return $this->display_num;
    }

    /**
     * @param int $display_num
     *
     * @return $this
     */
    public function setDisplayNum($display_num)
    {
        $this->display_num = $display_num;

        return $this;
    }

    /**
     * @return int
     */
    public function getRowNum()
    {
        return $this->row_num;
    }

    /**
     * @param int $row_num
     *
     * @return $this
     */
    public function setRowNum($row_num)
    {
        $this->row_num = $row_num;

        return $this;
    }

    /**
     * @return int
     */
    public function getColNum()
    {
        return $this->col_num;
    }

    /**
     * @param int $col_num
     *
     * @return $this
     */
    public function setColNum($col_num)
    {
        $this->col_num = $col_num;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayStyle()
    {
        return $this->display_style;
    }

    /**
     * @param string $display_style
     *
     * @return $this
     */
    public function setDisplayStyle($display_style)
    {
        $this->display_style = $display_style;

        return $this;
    }

    /**
     * @return Category
     */
    public function getDefaultCategory()
    {
        return $this->default_category;
    }

    /**
     * @param Category $default_category
     *
     * @return $this
     */
    public function setDefaultCategory($default_category)
    {
        $this->default_category = $default_category;

        return $this;
    }

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_on_top", type="boolean", options={"default":false})
     */
    private $show_on_top = false;

    /**
     * @return boolean
     */
    public function isShowOnTop()
    {
        return $this->show_on_top;
    }

    /**
     * @param boolean $show_on_top
     *
     * @return $this
     */
    public function setShowOnTop($show_on_top)
    {
        $this->show_on_top = $show_on_top;

        return $this;
    }
}