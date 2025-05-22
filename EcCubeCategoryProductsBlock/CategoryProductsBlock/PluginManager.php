<?php

namespace Plugin\CategoryProductsBlock;

use Eccube\Plugin\AbstractPluginManager;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\Layout;
use Psr\Container\ContainerInterface;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\PluginEvent;
use Plugin\CategoryProductsBlock\Entity\Config;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;
use Psr\Log\LoggerInterface;

class PluginManager extends AbstractPluginManager
{
    /**
     * Install the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function install(array $meta, ContainerInterface $container)
    {
        // ブロックの作成
        $this->createCategoryProductsBlock($container);
        
        // 設定の初期値を登録
        $this->createConfig($container);
    }

    /**
     * Update the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function update(array $meta, ContainerInterface $container)
    {
        // 既存の設定を保持したままアップデート
        $this->updateConfig($container);
    }

    /**
     * Enable the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function enable(array $meta, ContainerInterface $container)
    {
        // ブロックを有効化する処理
        $this->enableBlock($container);
        
        // キャッシュを削除
        if ($container->has('eccube.util.cache')) {
            $cacheUtil = $container->get('eccube.util.cache');
            if (method_exists($cacheUtil, 'clearCache')) {
                $cacheUtil->clearCache();
            }
        }
        
        // ロガーが存在する場合のみログを出力
        $this->log($container, 'カテゴリー別商品一覧ブロックが有効化されました。');
    }

    /**
     * Disable the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function disable(array $meta, ContainerInterface $container)
    {
        // ブロックを無効化する処理
        $this->disableBlock($container);
        
        // キャッシュを削除
        if ($container->has('eccube.util.cache')) {
            $cacheUtil = $container->get('eccube.util.cache');
            if (method_exists($cacheUtil, 'clearCache')) {
                $cacheUtil->clearCache();
            }
        }
        
        // ロガーが存在する場合のみログを出力
        $this->log($container, 'カテゴリー別商品一覧ブロックが無効化されました。');
    }

    /**
     * Uninstall the plugin.
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function uninstall(array $meta, ContainerInterface $container)
    {
        // ブロックの削除
        $this->removeCategoryProductsBlock($container);
        
        // 設定の削除
        $this->removeConfig($container);
    }

    
    /**
 * ブロックを有効化する
 *
 * @param ContainerInterface $container
 */
private function enableBlock(ContainerInterface $container)
{
    $entityManager = $container->get('doctrine.orm.entity_manager');
    $Block = $entityManager->getRepository(Block::class)->findOneBy(['file_name' => 'category_products']);
    
    if (!$Block) {
        // ブロックが存在しなければ作成
        $Block = $this->createCategoryProductsBlock($container);
    } else {
        // ブロックが存在すれば有効化
        // EC-CUBEバージョンによっては、setEnabledメソッドが存在しないかもしれないので確認
        if (method_exists($Block, 'setEnabled')) {
            $Block->setEnabled(true);
            $entityManager->flush();
        }
    }
    
    // BlockPosition作成は複雑なためスキップ
    // 管理者に手動でブロックを配置してもらう
    
    // ロガーが存在する場合のみログを出力
    $this->log($container, 'カテゴリー別商品一覧ブロックが有効化されました。手動でレイアウトに配置してください。');
}

    /**
     * ブロックを無効化する
     *
     * @param ContainerInterface $container
     */
    private function disableBlock(ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $Block = $entityManager->getRepository(Block::class)->findOneBy(['file_name' => 'category_products']);
        
        if ($Block) {
            // ブロックを無効化するだけで削除はしない
            // EC-CUBEバージョンによっては、setEnabledメソッドが存在しないかもしれないので確認
            if (method_exists($Block, 'setEnabled')) {
                $Block->setEnabled(false);
                $entityManager->flush();
            }
        }
    }

    /**
     * 設定を更新する
     *
     * @param ContainerInterface $container
     */
    private function updateConfig(ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        
        // get()メソッドが存在するか確認
        $configRepository = $entityManager->getRepository(Config::class);
        $Config = method_exists($configRepository, 'get') ? 
                 $configRepository->get() : 
                 $configRepository->findOneBy([]);
        
        // 既存の設定がなければ作成
        if (!$Config) {
            $this->createConfig($container);
            return;
        }
        
        // 新しい設定項目があれば追加
        // 例: display_styleがなければデフォルト値を設定
        if (method_exists($Config, 'setDisplayStyle') && 
           ((!method_exists($Config, 'getDisplayStyle')) || $Config->getDisplayStyle() === null)) {
            $Config->setDisplayStyle('grid');
        }
        
        // show_on_topプロパティを追加する処理
        if (method_exists($Config, 'setShowOnTop') && 
           ((!method_exists($Config, 'isShowOnTop')) || $Config->isShowOnTop() === null)) {
            $Config->setShowOnTop(false);
        }
        
        $entityManager->flush();
    }

    /**
     * 設定を削除する
     *
     * @param ContainerInterface $container
     */
    private function removeConfig(ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $configRepository = $entityManager->getRepository(Config::class);
        
        // get()メソッドが存在するか確認
        $Config = method_exists($configRepository, 'get') ? 
                 $configRepository->get() : 
                 $configRepository->findOneBy([]);
        
        if ($Config) {
            $entityManager->remove($Config);
            $entityManager->flush();
        }
    }


/**
 * カテゴリー商品ブロックを作成
 *
 * @param ContainerInterface $container
 * @return Block|null
 */
private function createCategoryProductsBlock(ContainerInterface $container)
{
    $entityManager = $container->get('doctrine.orm.entity_manager');
    
    // DeviceType定数が存在するか確認
    $deviceTypeId = defined('Eccube\Entity\Master\DeviceType::DEVICE_TYPE_PC') ? 
                   DeviceType::DEVICE_TYPE_PC : 
                   10; // EC-CUBEの標準値としてPC=10を使用
    
    $DeviceType = $entityManager->getRepository(DeviceType::class)->find($deviceTypeId);
    
    // ブロックの存在確認
    $Block = $entityManager->getRepository(Block::class)->findOneBy(['file_name' => 'category_products']);
    if ($Block) {
        return $Block;
    }
    
    // ブロックを作成
    $Block = new Block();
    $Block->setName('カテゴリー別商品一覧');
    $Block->setFileName('category_products'); // この名前が重要
    
    // setUseControllerメソッドが存在するか確認
    if (method_exists($Block, 'setUseController')) {
        $Block->setUseController(true);
    }
    
    // setDeletableメソッドが存在するか確認
    if (method_exists($Block, 'setDeletable')) {
        $Block->setDeletable(false);
    }
    
    // setDeviceTypeメソッドが存在するか確認
    if (method_exists($Block, 'setDeviceType') && $DeviceType) {
        $Block->setDeviceType($DeviceType);
    }
    
    // EC-CUBEバージョンによっては、setEnabledメソッドが存在しないかもしれないので確認
    if (method_exists($Block, 'setEnabled')) {
        $Block->setEnabled(true); // 有効化状態で作成
    }
    
    $entityManager->persist($Block);
    $entityManager->flush();
    
    // ログ出力
    $this->log($container, 'カテゴリー別商品一覧ブロックを追加しました。');
    
    return $Block;
}




    /**
     * カテゴリー商品ブロックを削除
     *
     * @param ContainerInterface $container
     */
    private function removeCategoryProductsBlock(ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $Block = $entityManager->getRepository(Block::class)->findOneBy(['file_name' => 'category_products']);
        if (!$Block) {
            return;
        }
        
        // ブロック配置を削除
        $blockPositions = $entityManager->getRepository(BlockPosition::class)->findBy(['Block' => $Block]);
        foreach ($blockPositions as $blockPosition) {
            $entityManager->remove($blockPosition);
        }
        
        // ブロックを削除
        $entityManager->remove($Block);
        $entityManager->flush();
        
        // ロガーが存在する場合のみログを出力
        $this->log($container, 'カテゴリー別商品一覧ブロックを削除しました。');
    }
    
    /**
     * 設定の初期値を登録
     *
     * @param ContainerInterface $container
     */
    private function createConfig(ContainerInterface $container)
    {
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $configRepository = $entityManager->getRepository(Config::class);
        
        // get()メソッドが存在するか確認
        $Config = method_exists($configRepository, 'get') ? 
                 $configRepository->get() : 
                 $configRepository->findOneBy([]);
        
        if ($Config) {
            return;
        }
        
        // デフォルトカテゴリは最初のカテゴリを使用
        $Category = $entityManager->getRepository('Eccube\Entity\Category')->findOneBy([], ['sort_no' => 'ASC']);
        
        $Config = new Config();
        
        // メソッドの存在を確認
        if (method_exists($Config, 'setDisplayNum')) {
            $Config->setDisplayNum(10); // デフォルト表示数
        }
        
        if (method_exists($Config, 'setRowNum')) {
            $Config->setRowNum(2);      // デフォルト行数
        }
        
        if (method_exists($Config, 'setColNum')) {
            $Config->setColNum(5);      // デフォルト列数
        }
        
        if (method_exists($Config, 'setDisplayStyle')) {
            $Config->setDisplayStyle('grid'); // デフォルト表示スタイル
        }
        
        if (method_exists($Config, 'setShowOnTop')) {
            $Config->setShowOnTop(false); // デフォルトではトップページ自動表示を無効
        }
        
        if (method_exists($Config, 'setDefaultCategory') && $Category) {
            $Config->setDefaultCategory($Category);
        }
        
        $entityManager->persist($Config);
        $entityManager->flush();
    }
    
    /**
     * ログを出力する（eccube.loggerが存在しない場合の対応）
     *
     * @param ContainerInterface $container
     * @param string $message
     */
    private function log(ContainerInterface $container, string $message)
    {
        // ロガーが存在する場合のみログを出力
        if ($container->has('eccube.logger')) {
            $container->get('eccube.logger')->info($message);
        } elseif ($container->has('logger')) {
            $container->get('logger')->info($message);
        } elseif ($container instanceof SymfonyContainerInterface && $container->has(LoggerInterface::class)) {
            $container->get(LoggerInterface::class)->info($message);
        }
        // ロガーが存在しない場合は何もしない
    }
}