<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Util\ArrayUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;

/**
 * Class HomeController
 * @package App\Controller
 */
class HomeController extends BaseController
{
    /**
     * @Route("/")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function index()
    {
        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => 'Empty'
        ]);
    }

    /**
     * @Route("/categories/", methods={"GET"})
     *
     * Get categories list
     *
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCategories(EntityManagerInterface $em)
    {
        $categories = $em->getRepository(Category::class)->findAllOrdered();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $categories,
        ]);
    }

    /**
     * @Route("/categories/", methods={"POST"})
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function createCategoryAction(EntityManagerInterface $em, Request $request)
    {
        $iRequest = json_decode($request->getContent(), true);
        $name = ArrayUtil::safe($iRequest, 'name', null);
        if (is_null($name)) {
            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'error' => 'El campo nombre es obligatorio',
            ]);
        }

        $category = new Category();
        $category->setName($name);
        $em->persist($category);
        $em->flush();

        return $this->json([
            'code'  => Response::HTTP_CREATED,
            'data'  => $category,
        ]);
    }

    /**
     * @Route("/subCategories/", methods={"GET"})
     *
     * @param EntityManagerInterface $em
     * @param $category
     */
    public function getSubCategoriesByParent(EntityManagerInterface $em)
    {
        $subCategories = $em->getRepository(SubCategory::class)->findAllOrdered();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $subCategories,
        ]);
    }

    /**
     * @Route("/subCategories/", methods={"POST"})
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function createSubCategoryAction(EntityManagerInterface $em, Request $request)
    {
        $iRequest = json_decode($request->getContent(), true);
        $category = ArrayUtil::safe($iRequest, 'category', -1);
        /** @var Category $category */
        $category = $em->getRepository(Category::class)->find($category);
        if (is_null($category)) {
            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'error' => 'No se ha encontrado la categoría seleccionada'
            ]);
        }

        $name = ArrayUtil::safe($iRequest, 'name', null);
        if (is_null($name)) {
            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'err'   => 'El nombre de la sub-categoría es obligatorio',
            ]);
        }

        $subcategory = new SubCategory();
        $subcategory
            ->setName($name)
            ->setCategory($category)
        ;

        $em->persist($subcategory);
        $em->flush();

        return $this->json([
            'code'  => Response::HTTP_CREATED,
            'data'  => $subcategory,
        ]);
    }
}
