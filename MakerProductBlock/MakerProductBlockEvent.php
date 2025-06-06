<?php

namespace Plugin\MakerProductBlock;

use Eccube\Event\TemplateEvent;
use Eccube\Event\EventArgs;
use Plugin\MakerProductBlock\Service\MakerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MakerProductBlockEvent implements EventSubscriberInterface
{
    /**
     * @var MakerService
     */
    protected $makerService;

    /**
     * MakerProductBlockEvent constructor.
     *
     * @param MakerService $makerService
     */
    public function __construct(MakerService $makerService)
    {
        $this->makerService = $makerService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Block/maker_product.twig' => 'onMakerProductBlockTwig',
            'snippet.twig' => 'onFrontSnippetTwig',
            'Product/list.twig' => 'onProductListTwig',
            'eccube.repository.product.search' => 'onProductSearch',
            'front.request.parameter' => 'onFrontRequestParameter',
        ];
    }

    public function onMakerProductBlockTwig(TemplateEvent $event)
    {
        // metaエラー対策
        if (!$event->hasParameter('meta')) {
            $event->setParameter('meta', []);
        }
        
        // パラメータの安全性確保
        $parameters = $event->getParameters();
        if (!isset($parameters['Products'])) {
            $parameters['Products'] = [];
            $event->setParameters($parameters);
        }
    }

    public function onFrontSnippetTwig(TemplateEvent $event)
    {
        // フロント画面でのカルーセルJS/CSS読み込み
        $snippet = '<link rel="stylesheet" href="/plugin/MakerProductBlock/css/maker-product-block.css">';
        $snippet .= '<script src="/plugin/MakerProductBlock/js/maker-product-carousel.js"></script>';
        $event->addSnippet($snippet);
    }
    
    /**
     * 商品一覧ページにメーカー名を表示
     */
    public function onProductListTwig(TemplateEvent $event)
    {
        $parameters = $event->getParameters();
        
        // メーカーIDの指定があるかチェック
        $request = $parameters['request'] ?? null;
        if ($request && $request->query->has('maker_id')) {
            $makerId = $request->query->get('maker_id');
            
            // メーカー名を取得
            $maker = $this->makerService->getMakerById($makerId);
            $makerName = $maker ? $maker->getName() : 'メーカー詳細';
            
            $parameters['maker_id'] = $makerId;
            $parameters['maker_name'] = $makerName;
            
            $event->setParameters($parameters);
            
            // メーカー名を表示するHTMLを追加
            $search = '<div class="ec-searchnavRole__counter">';
            $replace = '
            <div class="ec-searchnavRole__makerName">
                <span class="ec-font-bold">「' . $makerName . '」</span>の商品
            </div>
            ' . $search;
            
            $source = $event->getSource();
            $source = str_replace($search, $replace, $source);
            $event->setSource($source);
        }
    }
    
    /**
     * フロントのリクエストパラメータを処理
     */
    public function onFrontRequestParameter(EventArgs $event)
    {
        $request = $event->getRequest();
        
        // URLからmaker_idパラメータを取得
        if ($request->query->has('maker_id')) {
            $makerId = $request->query->get('maker_id');
            
            // SearchProductFormにデータを渡すため、searchData配列にmaker_idを設定
            $searchData = $request->get('search_data', []);
            $searchData['maker_id'] = $makerId;
            
            // 更新したsearch_dataをリクエストに設定
            $request->query->set('search_data', $searchData);
        }
    }
    
    /**
     * 商品検索のクエリビルダーを拡張
     */
    public function onProductSearch(EventArgs $event)
    {
        $searchData = $event->getArgument('searchData');
        $qb = $event->getArgument('qb');
        
        // メーカーIDでの絞り込み
        if (isset($searchData['maker_id']) && !empty($searchData['maker_id'])) {
            $makerId = $searchData['maker_id'];
            
            // NOTE: この部分はEC-CUBEとメーカープラグインの実装によって変わります
            // 例: 商品テーブルに maker_id フィールドがある場合
            $qb->andWhere('p.maker_id = :maker_id')
               ->setParameter('maker_id', $makerId);
            
            // 別の例: 中間テーブルを介してメーカーと関連付けられている場合
            // $qb->innerJoin('p.ProductMaker', 'pm')
            //    ->andWhere('pm.maker_id = :maker_id')
            //    ->setParameter('maker_id', $makerId);
            
            $event->setArgument('qb', $qb);
        }
    }
}