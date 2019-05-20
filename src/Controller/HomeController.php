<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SubCategory;
use Doctrine\ORM\EntityManagerInterface;
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
        $categories = $em->getRepository(Category::class)->findAll();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $categories,
        ]);
    }

    /**
     * @Route("/subCategories/")
     *
     * @param EntityManagerInterface $em
     * @param $category
     */
    public function getSubCategoriesByParent(EntityManagerInterface $em)
    {
        $subCategories = $em->getRepository(SubCategory::class)->findAll();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $subCategories,
        ]);
    }
}
