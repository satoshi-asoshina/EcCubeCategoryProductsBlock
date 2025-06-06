<?php

namespace Plugin\MakerProductBlock\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Block;
use Eccube\Entity\Master\DeviceType;
use Eccube\Repository\BlockRepository;
use Eccube\Repository\Master\DeviceTypeRepository;
use Plugin\MakerProductBlock\Entity\MakerBlock;
use Plugin\MakerProductBlock\Form\Type\Admin\ConfigType;
use Plugin\MakerProductBlock\Form\Type\Admin\SearchConfigType;
use Plugin\MakerProductBlock\Repository\MakerBlockRepository;
use Plugin\MakerProductBlock\Service\MakerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Eccube\Common\EccubeConfig;
use Symfony\Component\Form\FormFactoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/%eccube_admin_route%/maker_product_block")
 */
class ConfigController extends AbstractController
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
     * @var EntityManagerInterface
     */
    protected $entityManager;
    
    /**
     * @var BlockRepository
     */
    protected $blockRepository;
    
    /**
     * @var DeviceTypeRepository
     */
    protected $deviceTypeRepository;
    
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;
    
    /**
     * @var PaginatorInterface
     */
    protected $paginator;
    
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * ConfigController constructor.
     */
    public function __construct(
        MakerBlockRepository $makerBlockRepository,
        MakerService $makerService,
        EntityManagerInterface $entityManager,
        BlockRepository $blockRepository,
        DeviceTypeRepository $deviceTypeRepository,
        FormFactoryInterface $formFactory,
        PaginatorInterface $paginator,
        EccubeConfig $eccubeConfig
    ) {
        $this->makerBlockRepository = $makerBlockRepository;
        $this->makerService = $makerService;
        $this->entityManager = $entityManager;
        $this->blockRepository = $blockRepository;
        $this->deviceTypeRepository = $deviceTypeRepository;
        $this->formFactory = $formFactory;
        $this->paginator = $paginator;
        $this->eccubeConfig = $eccubeConfig;
    }

    /**
     * @Route("/", name="maker_product_block_admin_index", methods={"GET", "POST"})
     * @Template("@MakerProductBlock/admin/index.twig")
     */
    public function index(Request $request)
    {
        // 検索フォーム
        $searchForm = $this->formFactory
            ->createBuilder(SearchConfigType::class)
            ->getForm();
            
        $searchForm->handleRequest($request);
        $searchData = $searchForm->getData() ?: [];
        
        // ページネーション
        $qb = $this->makerBlockRepository->getAdminSearchQuery($searchData);
        $pagination = $this->paginator->paginate(
            $qb,
            $request->get('page', 1),
            $this->eccubeConfig['eccube_default_page_count']
        );
        
        // メーカー名の取得
        $makerNames = [];
        foreach ($pagination as $makerBlock) {
            $makerNames[$makerBlock->getId()] = $this->makerService->getMakerNameById($makerBlock->getMakerId());
        }
        
        return [
            'search_form' => $searchForm->createView(),
            'pagination' => $pagination,
            'makerNames' => $makerNames,
        ];
    }

    /**
     * @Route("/new", name="maker_product_block_admin_new", methods={"GET", "POST"})
     * @Template("@MakerProductBlock/admin/edit.twig")
     */
    public function create(Request $request)
    {
        $MakerBlock = new MakerBlock();
        $MakerBlock->setCreateDate(new \DateTime());
        $MakerBlock->setUpdateDate(new \DateTime());
        
        $form = $this->formFactory
            ->createBuilder(ConfigType::class, $MakerBlock)
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $MakerBlock = $form->getData();
            
            // ブロックの作成
            $Block = $this->createBlock($MakerBlock->getBlockName());
            $MakerBlock->setBlockId($Block->getId());
            
            $this->entityManager->persist($MakerBlock);
            $this->entityManager->flush();
            
            $this->addSuccess('メーカー商品ブロックを作成しました。', 'admin');
            
            return $this->redirectToRoute('maker_product_block_admin_index');
        }
        
        return [
            'form' => $form->createView(),
            'MakerBlock' => $MakerBlock,
        ];
    }

    /**
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="maker_product_block_admin_edit", methods={"GET", "POST"})
     * @Template("@MakerProductBlock/admin/edit.twig")
     */
    public function edit(Request $request, $id)
    {
        $MakerBlock = $this->makerBlockRepository->find($id);
        if (!$MakerBlock) {
            $this->addError('メーカー商品ブロックが見つかりません。', 'admin');
            return $this->redirectToRoute('maker_product_block_admin_index');
        }
        
        $MakerBlock->setUpdateDate(new \DateTime());
        
        $form = $this->formFactory
            ->createBuilder(ConfigType::class, $MakerBlock)
            ->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $MakerBlock = $form->getData();
            
            // ブロック名の更新
            $Block = $this->blockRepository->find($MakerBlock->getBlockId());
            if ($Block) {
                $Block->setName($MakerBlock->getBlockName());
                $this->entityManager->persist($Block);
            }
            
            $this->entityManager->persist($MakerBlock);
            $this->entityManager->flush();
            
            $this->addSuccess('メーカー商品ブロックを更新しました。', 'admin');
            
            return $this->redirectToRoute('maker_product_block_admin_index');
        }
        
        return [
            'form' => $form->createView(),
            'MakerBlock' => $MakerBlock,
        ];
    }

    /**
     * @Route("/{id}/delete", requirements={"id" = "\d+"}, name="maker_product_block_admin_delete", methods={"DELETE"})
     */
    public function delete(Request $request, $id)
    {
        $this->isTokenValid();
        
        $MakerBlock = $this->makerBlockRepository->find($id);
        if (!$MakerBlock) {
            $this->addError('メーカー商品ブロックが見つかりません。', 'admin');
            return $this->redirectToRoute('maker_product_block_admin_index');
        }
        
        // 関連ブロックの削除
        $Block = $this->blockRepository->find($MakerBlock->getBlockId());
        if ($Block) {
            $this->entityManager->remove($Block);
        }
        
        $this->entityManager->remove($MakerBlock);
        $this->entityManager->flush();
        
        $this->addSuccess('メーカー商品ブロックを削除しました。', 'admin');
        
        return $this->redirectToRoute('maker_product_block_admin_index');
    }

    /**
     * 動的ブロックを作成
     *
     * @param string $blockName
     * @return Block
     */
    private function createBlock($blockName)
    {
        // 一意のファイル名を生成
        $uniqueId = uniqid();
        $fileName = 'maker_product_' . $uniqueId;
        
        // 既存のブロックと重複しないか確認
        $existingBlock = $this->blockRepository->findBy([
            'file_name' => $fileName,
            'DeviceType' => $this->deviceTypeRepository->find(DeviceType::DEVICE_TYPE_PC)
        ]);
        
        // 万が一重複した場合は再生成
        if (!empty($existingBlock)) {
            $fileName = 'maker_product_' . uniqid('', true);
        }
        
        $Block = new Block();
        $Block->setName($blockName);
        $Block->setFileName($fileName);
        $Block->setUseController(true);
        $Block->setDeletable(true);
        $Block->setDeviceType($this->deviceTypeRepository->find(DeviceType::DEVICE_TYPE_PC));
        
        $this->entityManager->persist($Block);
        $this->entityManager->flush();
        
        return $Block;
    }
}
