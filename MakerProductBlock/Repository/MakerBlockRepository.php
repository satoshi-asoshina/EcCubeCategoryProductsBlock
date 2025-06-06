<?php

namespace Plugin\MakerProductBlock\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Eccube\Repository\AbstractRepository;
use Plugin\MakerProductBlock\Entity\MakerBlock;

/**
 * MakerBlockRepository
 */
class MakerBlockRepository extends AbstractRepository
{
    /**
     * コンストラクタ
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MakerBlock::class);
    }

    /**
     * 管理画面用の検索クエリを取得
     *
     * @param array $searchData
     * @return QueryBuilder
     */
    public function getAdminSearchQuery($searchData)
    {
        $qb = $this->createQueryBuilder('mb');
        
        // メーカー名での検索
        if (isset($searchData['maker_name']) && !empty($searchData['maker_name'])) {
            try {
                // メーカーエンティティへの結合
                $qb->innerJoin('Plugin\Maker42\Entity\Maker', 'm', 'WITH', 'mb.maker_id = m.id');
                $qb->andWhere('m.name LIKE :maker_name');
                $qb->setParameter('maker_name', '%' . $searchData['maker_name'] . '%');
            } catch (\Exception $e) {
                // メーカープラグインが利用できない場合は無視
            }
        }
        // ブロック名での検索
        if (isset($searchData['block_name']) && !empty($searchData['block_name'])) {
            $qb->andWhere('mb.block_name LIKE :block_name');
            $qb->setParameter('block_name', '%' . $searchData['block_name'] . '%');
        }
        
        // ステータスでの絞り込み
        if (isset($searchData['status']) && $searchData['status'] !== '') {
            $qb->andWhere('mb.is_enabled = :status');
            $qb->setParameter('status', $searchData['status']);
        }
        
        // ソート条件
        $qb->orderBy('mb.sort_no', 'ASC');
        $qb->addOrderBy('mb.id', 'DESC');
        
        return $qb;
    }

    /**
     * 有効なブロック一覧を取得
     *
     * @return MakerBlock[]
     */
    public function findActiveBlocks()
    {
        return $this->findBy(['is_enabled' => true], ['sort_no' => 'ASC']);
    }
}
