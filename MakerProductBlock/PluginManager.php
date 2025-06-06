<?php

namespace Plugin\MakerProductBlock;

use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Container\ContainerInterface;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class PluginManager extends AbstractPluginManager
{
    public function install(array $meta, ContainerInterface $container)
    {
        // テーブル作成
        $this->createTable($container);
    }

    public function enable(array $meta, ContainerInterface $container)
    {
        // コンテナからパラメータを取得する方法を修正
        $eccubeConfig = $container->get('Eccube\Common\EccubeConfig');
        $eccubeHtmlDir = $eccubeConfig->get('eccube_html_dir');
        $pluginRealDir = $eccubeConfig->get('plugin_realdir');
        
        // アセットディレクトリの作成
        $file = new Filesystem();
        $assetsDir = $eccubeHtmlDir . '/plugin/MakerProductBlock';
        
        if (!$file->exists($assetsDir)) {
            $file->mkdir($assetsDir);
            $file->mkdir($assetsDir . '/css');
            $file->mkdir($assetsDir . '/js');
        }
        
        // CSSファイルのコピー
        $file->copy(
            $pluginRealDir . '/MakerProductBlock/Resource/public/css/maker-product-block.css',
            $assetsDir . '/css/maker-product-block.css',
            true
        );
        
        // JSファイルのコピー
        $file->copy(
            $pluginRealDir . '/MakerProductBlock/Resource/public/js/maker-product-carousel.js',
            $assetsDir . '/js/maker-product-carousel.js',
            true
        );
        
        // テーブル作成（既存テーブルがない場合）
        $this->createTable($container);
    }
    
    public function disable(array $meta, ContainerInterface $container)
    {
        // 処理なし
    }
    
    public function uninstall(array $meta, ContainerInterface $container)
    {
        // コンテナからパラメータを取得する方法を修正
        $eccubeConfig = $container->get('Eccube\Common\EccubeConfig');
        $eccubeHtmlDir = $eccubeConfig->get('eccube_html_dir');
        
        // アセットディレクトリの削除
        $file = new Filesystem();
        $assetsDir = $eccubeHtmlDir . '/plugin/MakerProductBlock';
        
        if ($file->exists($assetsDir)) {
            $file->remove($assetsDir);
        }
        
        // テーブル削除
        $this->dropTable($container);
    }
    
    public function update(array $meta, ContainerInterface $container)
    {
        // テーブル再作成
        $this->createTable($container);
    }
    
    /**
     * プラグイン用テーブル作成
     *
     * @param ContainerInterface $container
     */
    private function createTable(ContainerInterface $container)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $connection = $entityManager->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        
        // テーブル存在確認
        $tableExists = false;
        try {
            $sql = $dbPlatform->getListTablesSQL();
            $tables = $connection->executeQuery($sql)->fetchAllAssociative();
            foreach ($tables as $table) {
                if (in_array('plg_maker_product_block', $table)) {
                    $tableExists = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            // エラー時は存在しないと仮定
        }
        
        // すでにテーブルが存在する場合は作成しない
        if ($tableExists) {
            return;
        }
        
        // Entityクラスからテーブル作成
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        
        $filteredMetaData = [];
        foreach ($metaData as $meta) {
            if (strpos($meta->getName(), 'Plugin\MakerProductBlock\Entity') === 0) {
                $filteredMetaData[] = $meta;
            }
        }
        
        if (!empty($filteredMetaData)) {
            $schemaTool->createSchema($filteredMetaData);
        }
    }
    
    /**
     * プラグイン用テーブル削除
     *
     * @param ContainerInterface $container
     */
    private function dropTable(ContainerInterface $container)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        
        $filteredMetaData = [];
        foreach ($metaData as $meta) {
            if (strpos($meta->getName(), 'Plugin\MakerProductBlock\Entity') === 0) {
                $filteredMetaData[] = $meta;
            }
        }
        
        if (!empty($filteredMetaData)) {
            $schemaTool->dropSchema($filteredMetaData);
        }
    }
}