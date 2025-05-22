<?php
// ブロック表示のロジックをプラグイン内で完結させるため、TwigExtensionを作成

namespace Plugin\CategoryProductsBlock\Twig;

use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductRepository;
use Plugin\CategoryProductsBlock\Repository\ConfigRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategoryProductsExtension extends AbstractExtension
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * Constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param ConfigRepository $configRepository
     * @param \Twig\Environment $twig
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ConfigRepository $configRepository,
        \Twig\Environment $twig
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->configRepository = $configRepository;
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('category_products_block', [$this, 'getCategoryProductsBlock']),
        ];
    }

    /**
     * カテゴリー商品ブロックを取得
     *
     * @param int $categoryId
     * @return string
     */
    public function getCategoryProductsBlock($categoryId = null)
    {
        // 設定取得
        $Config = $this->configRepository->get();
        $limit = $Config ? $Config->getDisplayNum() : 10;
        $rowNum = $Config ? $Config->getRowNum() : 2;
        $colNum = $Config ? $Config->getColNum() : 5;
        $displayStyle = $Config ? $Config->getDisplayStyle() : 'grid';
        
        // カテゴリー取得
        $Categories = $this->categoryRepository->getList(null, true);
        
        // デフォルトカテゴリーを使用
        $defaultCategory = $Config && $Config->getDefaultCategory() 
            ? $Config->getDefaultCategory() 
            : (count($Categories) > 0 ? $Categories[0] : null);
        
        $defaultCategoryId = $defaultCategory ? $defaultCategory->getId() : null;
        
        // カテゴリーIDが指定されていれば使用
        $categoryId = $categoryId ?: $defaultCategoryId;
        $Category = $this->categoryRepository->find($categoryId);
        
        if (!$Category) {
            return '';
        }
        
        // 商品取得
        $qb = $this->productRepository->getQueryBuilderBySearchData(['category_id' => $categoryId]);
        $Products = $qb->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        
        // テンプレートをレンダリング
        return $this->twig->render('@CategoryProductsBlock/Block/category_products.twig', [
            'Categories' => $Categories,
            'Products' => $Products,
            'CurrentCategory' => $Category,
            'displayStyle' => $displayStyle,
            'rowNum' => $rowNum,
            'colNum' => $colNum,
        ]);
    }
}