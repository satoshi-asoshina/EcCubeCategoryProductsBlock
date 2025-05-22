<?php

namespace Plugin\CategoryProductsBlock\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\CategoryProductsBlock\Form\Type\Admin\ConfigType;
use Plugin\CategoryProductsBlock\Repository\ConfigRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/%eccube_admin_route%/category_products_block')]
class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * ConfigController constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    #[Route('/config', name: 'category_products_block_admin_config')]
    public function index(Request $request): Response
    {
        $Config = $this->configRepository->get();
        if (!$Config) {
            $Config = new \Plugin\CategoryProductsBlock\Entity\Config();
        }

        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush();
            
            $this->addSuccess('登録しました。', 'admin');

            return $this->redirectToRoute('category_products_block_admin_config');
        }

        return $this->render('CategoryProductsBlock/Resource/template/admin/config.twig', [
            'form' => $form->createView(),
        ]);
    }
}