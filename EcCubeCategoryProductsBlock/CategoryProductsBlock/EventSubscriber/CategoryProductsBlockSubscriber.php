<?php

namespace Plugin\CategoryProductsBlock\EventSubscriber;

use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductRepository;
use Plugin\CategoryProductsBlock\Repository\ConfigRepository;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Asset\Packages;

class CategoryProductsBlockSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var Packages
     */
    protected $assetPackages;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository 
     * @param ConfigRepository $configRepository
     * @param \Twig\Environment $twig
     * @param EccubeConfig $eccubeConfig
     * @param Packages $assetPackages
     */
    public function __construct(
        ContainerInterface $container,
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        ConfigRepository $configRepository,
        \Twig\Environment $twig,
        EccubeConfig $eccubeConfig,
        Packages $assetPackages
    ) {
        $this->container = $container;
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->configRepository = $configRepository;
        $this->twig = $twig;
        $this->eccubeConfig = $eccubeConfig;
        $this->assetPackages = $assetPackages;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // 商品一覧ページの変更イベント
            'Product/list.twig' => 'onProductListRender',
            
            // 商品詳細ページの変更イベント
            'Product/detail.twig' => 'onProductDetailRender',
            
            // 管理画面メニュー追加
            '@admin/nav.twig' => 'onAdminNavRender',
            
            // 初期化イベント
            KernelEvents::REQUEST => 'onKernelRequest',
            
            // ヘッダー部分にCSSとJSを追加
            'block.head' => 'onHeadResponse',
            
            // トップページへの追加
            'index.twig' => 'onTopPageRender',
        ];
    }

    /**
     * 商品一覧ページのイベント処理
     * 
     * カテゴリ商品一覧ページのパンくずリストの下に関連カテゴリータグを表示
     *
     * @param TemplateEvent $event
     */
    public function onProductListRender(TemplateEvent $event)
    {
        $parameters = $event->getParameters();
        
        // カテゴリーが設定されている場合のみ実行
        if (isset($parameters['Category']) && $parameters['Category']) {
            $Category = $parameters['Category'];
            
            // 親カテゴリーか最上位カテゴリーの取得
            $parentCategory = $Category->getParent();
            $targetCategory = $parentCategory ? $parentCategory : $Category;
            
            // 同階層のカテゴリーを取得
            $Categories = $this->categoryRepository->getList($targetCategory);
            
            // CSS ID用のランダム文字列
            $randomId = 'category-tags-' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
            
            // カテゴリータグテンプレートのレンダリング
            $snippet = $this->twig->render('@CategoryProductsBlock/category_tags.twig', [
                'Categories' => $Categories,
                'CurrentCategory' => $Category,
                'randomId' => $randomId
            ]);
            
            // パンくずリスト(<ol class="ec-topicpath">)の後にタグを挿入
            $search = '<ol class="ec-topicpath">';
            $replace = $search;
            $source = $event->getSource();
            
            // パンくずリストの後にカテゴリータグを表示
            $pos = strpos($source, '</ol>');
            if ($pos !== false) {
                $replace = substr($source, 0, $pos + 5) . $snippet;
                $source = substr_replace($source, $replace, 0, $pos + 5);
                $event->setSource($source);
            }
        }
    }

    /**
     * 商品詳細ページのイベント処理
     * 
     * 商品詳細ページに同カテゴリーの関連商品を表示
     *
     * @param TemplateEvent $event
     */
    public function onProductDetailRender(TemplateEvent $event)
    {
        $parameters = $event->getParameters();
        
        if (isset($parameters['Product']) && $parameters['Product']) {
            $Product = $parameters['Product'];
            
            // 商品の最初のカテゴリーを取得
            $ProductCategories = $Product->getProductCategories();
            if (count($ProductCategories) > 0) {
                $Category = $ProductCategories[0]->getCategory();
                
                // 設定を取得
                $Config = $this->configRepository->get();
                $limit = $Config ? $Config->getDisplayNum() : 10;
                
                // 同カテゴリーの関連商品を取得（現在の商品を除く）
                $qb = $this->productRepository->getQueryBuilderBySearchData(['category_id' => $Category->getId()]);
                $qb->andWhere('p.id != :product_id')
                   ->setParameter('product_id', $Product->getId())
                   ->setMaxResults($limit);
                
                $relatedProducts = $qb->getQuery()->getResult();
                
                if (count($relatedProducts) > 0) {
                    // 関連商品テンプレートのレンダリング
                    $snippet = $this->twig->render('@CategoryProductsBlock/related_products.twig', [
                        'RelatedProducts' => $relatedProducts,
                        'Category' => $Category
                    ]);
                    
                    // 商品詳細情報の後に関連商品を表示
                    $search = '<div class="ec-productRole__description">';
                    $pos = strpos($event->getSource(), $search);
                    
                    if ($pos !== false) {
                        // 商品説明の後に関連商品セクションを追加
                        $endPos = strpos($event->getSource(), '</div>', $pos);
                        if ($endPos !== false) {
                            $replace = substr($event->getSource(), 0, $endPos + 6) . $snippet;
                            $source = substr_replace($event->getSource(), $replace, 0, $endPos + 6);
                            $event->setSource($source);
                        }
                    }
                }
            }
        }
    }

    /**
     * 管理画面メニュー追加
     *
     * @param TemplateEvent $event
     */
    public function onAdminNavRender(TemplateEvent $event)
    {
        // 管理画面のナビゲーションにメニューを追加
        $twig = '@CategoryProductsBlock/admin/nav.twig';
        $event->addSnippet($twig);
    }

    /**
     * ヘッダーにCSSとJSを追加
     *
     * @param TemplateEvent $event
     */
    public function onHeadResponse(TemplateEvent $event)
    {
        // プラグイン用のCSSを追加
        $css = '<link rel="stylesheet" href="' . $this->assetPackages->getUrl('assets/css/category_products_block.css', 'plugin') . '">';
        $event->addSnippet($css);
        
        // プラグイン用のJavaScriptを追加
        $js = '<script src="' . $this->assetPackages->getUrl('assets/js/category_products_block.js', 'plugin') . '"></script>';
        $event->addSnippet($js);
    }

    /**
     * トップページに商品カテゴリーブロックを追加
     *
     * @param TemplateEvent $event
     */
    public function onTopPageRender(TemplateEvent $event)
    {
        // すでにブロックとして配置されている場合は不要なので、
        // 設定で「トップページに自動表示」が有効になっている場合のみ実行
        $Config = $this->configRepository->get();
        if ($Config && $Config->isShowOnTop()) {
            // 設定から取得
            $limit = $Config->getDisplayNum();
            $rowNum = $Config->getRowNum();
            $colNum = $Config->getColNum();
            $displayStyle = $Config->getDisplayStyle();
            
            // すべてのカテゴリを取得
            $Categories = $this->categoryRepository->getList(null, true);
            
            // デフォルトカテゴリを使用
            $defaultCategory = $Config->getDefaultCategory() 
                ? $Config->getDefaultCategory() 
                : (count($Categories) > 0 ? $Categories[0] : null);
            
            if ($defaultCategory) {
                // 選択されたカテゴリの商品を取得
                $qb = $this->productRepository->getQueryBuilderBySearchData(['category_id' => $defaultCategory->getId()]);
                $Products = $qb->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
                
                // カテゴリー商品ブロックのテンプレートをレンダリング
                $snippet = $this->twig->render('@CategoryProductsBlock/Block/category_products.twig', [
                    'Categories' => $Categories,
                    'Products' => $Products,
                    'CurrentCategory' => $defaultCategory,
                    'displayStyle' => $displayStyle,
                    'rowNum' => $rowNum,
                    'colNum' => $colNum,
                ]);
                
                // 新着商品セクションの後にカテゴリー商品ブロックを挿入
                $search = '<div class="ec-role">
        <section class="ec-newItemRole">';
                $pos = strpos($event->getSource(), $search);
                
                if ($pos !== false) {
                    // 新着商品セクションの終了タグを探す
                    $endPos = strpos($event->getSource(), '</section>', $pos);
                    if ($endPos !== false) {
                        $endPos = strpos($event->getSource(), '</div>', $endPos);
                        if ($endPos !== false) {
                            $replace = substr($event->getSource(), 0, $endPos + 6) . 
                                       '<div class="ec-role"><section class="ec-categoryProductsRole">' . 
                                       $snippet . 
                                       '</section></div>';
                            $source = substr_replace($event->getSource(), $replace, 0, $endPos + 6);
                            $event->setSource($source);
                        }
                    }
                }
            }
        }
    }

    /**
     * カーネルリクエストイベント
     * 
     * プラグインの初期化処理
     *
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }
        
        // アセットディレクトリの作成
        $this->createAssetDirectory();
        
        // 必要であればCSS/JSファイルのコピーを実行
        $this->copyAssetFiles();
    }
    
    /**
     * アセットディレクトリを作成
     */
    private function createAssetDirectory()
    {
        $pluginAssetPath = $this->eccubeConfig->get('plugin_html_realdir') . '/CategoryProductsBlock/assets';
        if (!file_exists($pluginAssetPath)) {
            mkdir($pluginAssetPath, 0777, true);
        }
        
        // CSS/JSディレクトリ
        $cssDir = $pluginAssetPath . '/css';
        if (!file_exists($cssDir)) {
            mkdir($cssDir, 0777, true);
        }
        
        $jsDir = $pluginAssetPath . '/js';
        if (!file_exists($jsDir)) {
            mkdir($jsDir, 0777, true);
        }
    }
    
    /**
     * アセットファイルをコピー
     */
    private function copyAssetFiles()
    {
        // プラグインのアセットソースパス
        $srcPath = $this->container->getParameter('kernel.project_dir') . '/app/Plugin/CategoryProductsBlock/Resource/assets';
        
        // コピー先パス
        $dstPath = $this->eccubeConfig->get('plugin_html_realdir') . '/CategoryProductsBlock/assets';
        
        // ソースCSSファイルのパス
        $srcCssPath = $srcPath . '/style.scss';
        
        // ターゲットCSSファイルのパス
        $dstCssPath = $dstPath . '/css/category_products_block.css';
        
        // CSSファイルが存在していない場合、またはソースの方が新しい場合にコピー
        if (!file_exists($dstCssPath) || (file_exists($srcCssPath) && filemtime($srcCssPath) > filemtime($dstCssPath))) {
            // SCSSをコンパイルしてCSSにする（実際はcompileを使用すべきですが、簡易的な例として）
            $cssContent = file_get_contents($srcCssPath);
            
            // CSSファイルに保存
            file_put_contents($dstCssPath, $cssContent);
        }
        
        // JSファイルも同様にコピー
        $srcJsPath = $srcPath . '/category_products_block.js';
        $dstJsPath = $dstPath . '/js/category_products_block.js';
        
        if (file_exists($srcJsPath) && (!file_exists($dstJsPath) || filemtime($srcJsPath) > filemtime($dstJsPath))) {
            copy($srcJsPath, $dstJsPath);
        }
    }
}