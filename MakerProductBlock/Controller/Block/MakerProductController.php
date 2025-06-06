<?php
// 2
namespace Plugin\MakerProductBlock\Controller\Block;

use Eccube\Controller\AbstractController;
use Plugin\MakerProductBlock\Repository\MakerBlockRepository;
use Plugin\MakerProductBlock\Service\MakerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * メーカー商品ブロックコントローラ
 */
class MakerProductController extends AbstractController
{
    /**
     * @var MakerBlockRepository
     */
    protected $makerBlockRepository;
    
    /**
     * @var MakerService
     */
    protected $makerService;
    
    /**
     * MakerProductController constructor.
     *
     * @param MakerBlockRepository $makerBlockRepository
     * @param MakerService $makerService
     */
    public function __construct(
        MakerBlockRepository $makerBlockRepository,
        MakerService $makerService
    ) {
        $this->makerBlockRepository = $makerBlockRepository;
        $this->makerService = $makerService;
    }
    
    /**
     * @Route("block/maker_product/{id}", requirements={"id"="\w+"}, name="block_maker_product", methods={"GET"})
     */
    public function index(Request $request, $id = null)
    {
        // IDからmachine_idとblock_idを抽出
        // ファイル名がmaker_product_{uniqid}の形式なので、プラグインのIDをDBから取得
        $Block = $this->entityManager->getRepository('Eccube\Entity\Block')
            ->findOneBy(['file_name' => 'maker_product_' . $id]);
        
        if (!$Block) {
            return $this->render('@MakerProductBlock/Block/maker_product.twig', [
                'Products' => [],
                'maker_name' => '',
                'visible_count' => 4,
                'visible_count_sp' => 1,
            ]);
        }
        
        // ブロックIDからメーカーブロック情報を取得
        $MakerBlock = $this->makerBlockRepository->findOneBy(['block_id' => $Block->getId()]);
        if (!$MakerBlock || !$MakerBlock->isEnabled()) {
            return $this->render('@MakerProductBlock/Block/maker_product.twig', [
                'Products' => [],
                'maker_name' => '',
                'visible_count' => 4,
                'visible_count_sp' => 1,
            ]);
        }
        
        // メーカー情報を取得
        $Maker = $this->makerService->getMakerById($MakerBlock->getMakerId());
        if (!$Maker) {
            return $this->render('@MakerProductBlock/Block/maker_product.twig', [
                'Products' => [],
                'maker_name' => '',
                'maker_id' => $MakerBlock->getMakerId(),
                'block_id' => $MakerBlock->getId(),
                'visible_count' => $MakerBlock->getVisibleCount(),
                'visible_count_sp' => $MakerBlock->getVisibleCountSp(),
            ]);
        }
        
        // 商品情報を取得
        $Products = $this->makerService->getProductsByMakerId(
            $MakerBlock->getMakerId(),
            $MakerBlock->getProductCount(),
            $MakerBlock->getSortType()
        );
        
        return $this->render('@MakerProductBlock/Block/maker_product.twig', [
            'maker_id' => $MakerBlock->getMakerId(),
            'maker_name' => $Maker->getName(),
            'Products' => $Products,
            'block_id' => $MakerBlock->getId(),
            'visible_count' => $MakerBlock->getVisibleCount(),
            'visible_count_sp' => $MakerBlock->getVisibleCountSp(),
        ]);
    }
}