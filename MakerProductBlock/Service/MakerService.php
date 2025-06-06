<?php

namespace Plugin\MakerProductBlock\Service;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\ProductRepository;

/**
 * メーカープラグインとの連携サービス
 */
class MakerService
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * MakerService constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProductRepository $productRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository
    ) {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
    }
    
    /**
     * 利用可能なメーカー一覧を取得
     *
     * @return array
     */
    public function getAvailableMakers()
    {
        try {
            return $this->entityManager->getRepository('Plugin\Maker42\Entity\Maker')
                ->findBy([], ['sort_no' => 'ASC']);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * メーカーIDから商品一覧を取得
     *
     * @param int $makerId
     * @param int $limit
     * @param string $sortType
     * @return array
     */
    public function getProductsByMakerId($makerId, $limit = 5, $sortType = 'new')
    {
        try {
            $qb = $this->productRepository->createQueryBuilder('p')
                ->where('p.Maker = :makerId')
                ->andWhere('p.Status = 1') // 公開状態
                ->setParameter('makerId', $makerId);
            
            // ソート条件
            switch ($sortType) {
                case 'price':
                    $qb->orderBy('p.price02', 'ASC');
                    break;
                case 'stock':
                    $qb->innerJoin('p.ProductClasses', 'pc')
                       ->innerJoin('pc.ProductStock', 'ps')
                       ->orderBy('ps.stock', 'DESC');
                    break;
                case 'new':
                default:
                    $qb->orderBy('p.create_date', 'DESC');
                    break;
            }
            
            return $qb->setMaxResults($limit)
                ->getQuery()
                ->getResult();
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * メーカー情報を取得
     *
     * @param int $makerId
     * @return mixed|null
     */
    public function getMakerById($makerId)
    {
        try {
            return $this->entityManager->getRepository('Plugin\Maker42\Entity\Maker')
                ->find($makerId);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * メーカーIDからメーカー名を取得
     *
     * @param int $makerId
     * @return string
     */
    public function getMakerNameById($makerId)
    {
        try {
            $maker = $this->getMakerById($makerId);
            return $maker ? $maker->getName() : '未設定';
        } catch (\Exception $e) {
            return '未設定';
        }
    }
}
