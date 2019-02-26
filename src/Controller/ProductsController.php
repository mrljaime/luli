<?php
/**
 * @author <a href="mailto:mr.ljaime@gmail.com">José Jaime Ramírez Calvo</a>
 * @version 1
 * @since 2019-02-24
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Provider;
use App\Entity\SubCategory;
use App\Util\DateTimeUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ProductsController
 * @package App\Controller
 * @Route("/products")
 */
class ProductsController extends BaseController
{
    /**
     * @Route("/", name="products.list", methods={"GET"})
     * Get all products
     *
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listAction(EntityManagerInterface $em)
    {
        $products = $em->getRepository(Product::class)->findAll();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $products,
        ]);
    }

    /**
     * Get product by id
     * @Route("/{product}", name="products.get", methods={"GET"})
     *
     * @param $product
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAction($product, EntityManagerInterface $em)
    {
        /** @var Product $product */
        $product = $em->getRepository(Product::class)->find($product);
        if (is_null($product)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado el producto',
            ]);
        }

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $product,
        ]);
    }

    /**
     * Get products by provider
     * @Route("/find", name="products.find", methods={"GET"})
     *
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getByProvider(Request $request, EntityManagerInterface $em)
    {
        /** @var string $provider */
        $provider = $request->get('provider');
        $query = $request->get('q', '');

        /** @var Provider $provider */
        $provider = $em->getRepository(Provider::class)->find($provider);
        if (is_null($provider)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encotrado el recurso solicitado',
            ]);
        }

        /** @var Product[] $products */
        $products = $em->getRepository(Product::class)->searchByProvider($provider, $query);

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $products,
        ]);
    }

    /**
     * Create a new product
     * @Route("/", name="products.create", methods={"POST"})
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addAction(Request $request, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $provider = $request->get('provider');
        $category = $request->get('category');
        $subCategory = $request->get('subCategory');

        /** @var Provider $provider */
        $provider = $em->getRepository(Provider::class)->find($provider);
        if (is_null($provider)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado el proveedor',
            ]);
        }

        /** @var Category $category */
        $category = $em->getRepository(Category::class)->find($category);
        if (is_null($category)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha enctrado la categoría'
            ]);
        }

        // To check that sub category is child of category
        /** @var SubCategory $subCategory */
        $subCategory = $em->getRepository(SubCategory::class)->findOneBy([
            'category'  => $category,
            'id'        => $subCategory
        ]);
        if (is_null($subCategory)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado la sub categoría o no es parte de la categoría'
            ]);
        }

        $name = $request->get('name');
        $description = $request->get('description');
        $price = $request->get('price');
        $qty = $request->get('qty');
        $active = $request->get('active');

        $product = new Product();
        $product
            ->setName($name)
            ->setDescription($description)
            ->setPrice($price)
            ->setQty($qty)
            ->setActive($active)
            ->setCategory($category)
            ->setSubCategory($subCategory)
            ->setProvider($provider)
        ;

        $validations = $validator->validate($product);
        if (0 < count($validations)) {
            $errors = [];
            foreach ($validations as $validation) {
                $errors[] = $validation->getMessage();
            }

            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'error' => $errors
            ]);
        }

        $em->persist($product);
        $em->flush();

        return $this->json([
            'code'  => Response::HTTP_CREATED,
            'data'  => [
                'id'    => $product->getId(),
            ]
        ]);
    }

    /**
     * Update a product
     * @Route("/{product}", name="products.update", methods={"PUT"})
     *
     * @param $product
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function updateAction(
        $product,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        LoggerInterface $logger
    ) {
        /** @var Product $product */
        $product = $em->getRepository(Product::class)->find($product);
        if (is_null($product)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado el producto solicitado'
            ]);
        }

        $provider = $request->get('provider');
        /** @var Provider $provider */
        $provider = $em->getRepository(Provider::class)->find($provider);
        if (is_null($provider)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado el proveedor'
            ]);
        }

        $category = $request->get('category');
        $subCategory = $request->get('subCategory');
        /** @var Category $category */
        $category = $em->getRepository(Category::class)->find($category);
        if (is_null($category)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado la categoría',
            ]);
        }
        /** @var SubCategory $subCategory */
        $subCategory = $em->getRepository(SubCategory::class)->findOneBy([
            'id'        => $subCategory,
            'category'  => $category
        ]);
        if (is_null($subCategory)) {
            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado la sub categoría o no es parte de la categoría'
            ]);
        }

        $name = $request->get('name');
        $description = $request->get('description');
        $price = $request->get('price');
        $qty = $request->get('qty');    // TODO: Check that qty can be updated without any validation
        $active = $request->get('active');

        $product
            ->setName($name)
            ->setDescription($description)
            ->setPrice($price)
            ->setQty($qty)
            ->setActive($active)
            ->setCategory($category)
            ->setSubCategory($subCategory)
            ->setProvider($provider)
            ->setUpdatedAt(DateTimeUtil::getDateTime())
        ;

        /**
         * **********
         * BEGIN TRANSACTION
         * **********
         */
        $em->beginTransaction();
        try {
            $em->persist($product);
            $em->flush();
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
            $logger->error($exception->getTraceAsString());

            /**
             * **********
             * ROLLBACK
             * **********
             */
            $em->rollback();

            return $this->json([
                'code'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => 'Ha habido un error interno. Contacte a un administrador'
            ]);
        }
        /**
         * **********
         * COMMIT
         * **********
         */
        $em->commit();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => [
                'id'        => $product->getId(),
                'updatedAt' => DateTimeUtil::formatForJsonResponse($product->getUpdatedAt())
            ]
        ]);
    }
}