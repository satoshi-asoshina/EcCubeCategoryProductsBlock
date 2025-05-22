<?php

namespace Plugin\CategoryProductsBlock\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductRepository;
use Plugin\CategoryProductsBlock\Repository\ConfigRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/block")
 */
#[Route('/block')]
class CategoryProductsController extends AbstractController
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
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param ConfigRepository $configRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ConfigRepository $configRepository,
        LoggerInterface $logger = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->configRepository = $configRepository;
        $this->logger = $logger;
    }

    /**
     * @Route("/category_products", name="block_category_products")
     */
    #[Route('/category_products', name: 'block_category_products')]
    public function index(Request $request): Response
    {
        try {
            // デバッグログ
            if ($this->logger) {
                $this->logger->info('カテゴリー商品ブロックが呼び出されました。');
            }
            
            // 設定から取得
            $Config = $this->configRepository->get();
            $limit = $Config ? $Config->getDisplayNum() : 10;
            $rowNum = $Config ? $Config->getRowNum() : 2;
            $colNum = $Config ? $Config->getColNum() : 5;
            $displayStyle = $Config ? $Config->getDisplayStyle() : 'grid';
            
            // すべてのカテゴリを取得
            $Categories = [];
            try {
                $Categories = $this->categoryRepository->getList(null, true);
            } catch (\Exception $e) {
                // エラーハンドリング
                if ($this->logger) {
                    $this->logger->error('カテゴリの取得に失敗しました: ' . $e->getMessage());
                }
            }
            
            // デフォルトカテゴリを使用
            $defaultCategoryId = null;
            $defaultCategory = null;
            
            if ($Config && method_exists($Config, 'getDefaultCategory')) {
                $defaultCategoryValue = $Config->getDefaultCategory();
                if (is_object($defaultCategoryValue) && method_exists($defaultCategoryValue, 'getId')) {
                    $defaultCategory = $defaultCategoryValue;
                    $defaultCategoryId = $defaultCategory->getId();
                } elseif (is_numeric($defaultCategoryValue)) {
                    $defaultCategoryId = (int)$defaultCategoryValue;
                    try {
                        $defaultCategory = $this->categoryRepository->find($defaultCategoryId);
                    } catch (\Exception $e) {
                        // エラーハンドリング
                        if ($this->logger) {
                            $this->logger->error('デフォルトカテゴリIDからオブジェクトの取得に失敗しました: ' . $e->getMessage());
                        }
                    }
                }
            }
            
            // デフォルトカテゴリが設定されていない場合
            if (!$defaultCategory && !empty($Categories)) {
                $defaultCategory = $Categories[0];
                if (is_object($defaultCategory) && method_exists($defaultCategory, 'getId')) {
                    $defaultCategoryId = $defaultCategory->getId();
                }
            }
            
            // カテゴリID指定があればそれを使用（Ajax用）
            $categoryId = $request->get('category_id', $defaultCategoryId);
            $Category = null;
            
            if ($categoryId) {
                try {
                    // 整数型に変換
                    if (is_string($categoryId)) {
                        $categoryId = (int)$categoryId;
                    }
                    
                    // カテゴリエンティティの取得
                    $Category = $this->categoryRepository->find($categoryId);
                    
                    // オブジェクトのチェック
                    if (!is_object($Category) || !method_exists($Category, 'getId')) {
                        // カテゴリが取得できなかった場合
                        if ($this->logger) {
                            $this->logger->warning('カテゴリID ' . $categoryId . ' からカテゴリオブジェクトが取得できませんでした');
                        }
                        $Category = null;
                    }
                } catch (\Exception $e) {
                    // エラーハンドリング
                    if ($this->logger) {
                        $this->logger->error('カテゴリIDの検索に失敗しました: ' . $e->getMessage());
                    }
                    $Category = null;
                }
            }
            
            // カテゴリが取得できなかった場合のフォールバック
            if (!$Category && !empty($Categories)) {
                $Category = $Categories[0];
                if (!is_object($Category) || !method_exists($Category, 'getId')) {
                    throw new \Exception('有効なカテゴリを取得できませんでした');
                }
            }
            
            // ここでもカテゴリが取得できなければエラー
            if (!$Category) {
                throw new \Exception('カテゴリが取得できませんでした');
            }
            
            // 商品を取得
            $Products = [];
            try {
                $searchData = ['category_id' => $Category->getId()];
                $qb = $this->productRepository->getQueryBuilderBySearchData($searchData);
                $Products = $qb->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
            } catch (\Exception $e) {
                // エラーハンドリング
                if ($this->logger) {
                    $this->logger->error('商品の取得に失敗しました: ' . $e->getMessage());
                }
            }
            
            // Ajaxリクエストの場合は部分レスポンスを返す
            if ($request->isXmlHttpRequest()) {
                return $this->render('@CategoryProductsBlock/Block/category_products_items.twig', [
                    'Products' => $Products,
                    'Category' => $Category,
                    'displayStyle' => $displayStyle,
                    'rowNum' => $rowNum,
                    'colNum' => $colNum,
                ]);
            }
            
            // 通常のレスポンス
            return $this->render('@CategoryProductsBlock/Block/category_products.twig', [
                'Categories' => $Categories,
                'Products' => $Products,
                'CurrentCategory' => $Category,
                'displayStyle' => $displayStyle,
                'rowNum' => $rowNum,
                'colNum' => $colNum,
            ]);
            
        } catch (\Exception $e) {
            // 全体的なエラーハンドリング
            if ($this->logger) {
                $this->logger->error('カテゴリー商品ブロックの表示に失敗しました: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            }
            
            // 静的なフォールバックを返す
            $html = '<div class="ec-categoryProductsBlock ec-categoryProductsBlock--fallback">';
            $html .= '<p>カテゴリー商品ブロックの読み込みに失敗しました。</p>';
            $html .= '</div>';
            
            return new Response($html);
        }
    }
}