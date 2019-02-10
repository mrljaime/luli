<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 */

namespace App\Controller;

use App\Entity\Provider;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class ProvidersController
 * @package App\Controller
 *
 * @Route("/providers")
 */
class ProvidersController extends BaseController
{
    /**
     * @Route("/", name="providers.list", methods={"GET"})
     *
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listAction(EntityManagerInterface $em)
    {
        /** @var Provider[] $providers */
        $providers = $em->getRepository(Provider::class)->findActive();
        $data = [];
        foreach ($providers as $provider) {
            $data[] = [
                'id'            => $provider->getId(),
                'name'          => $provider->getName(),
                'email'         => $provider->getEmail(),
                'status'        => $provider->getStatus(),
                'phoneNumber'   => $provider->getPhoneNumber(),
                'uniqueId'      => $provider->getUniqueIdentifier(),
            ];
        }

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $data,
        ]);
    }

    /**
     * @Route("/{id}", name="providers.get", methods={"GET"})
     *
     * @param EntityManagerInterface $em
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAction(EntityManagerInterface $em, $id)
    {
        /** @var Provider $provider */
        $provider = $em->getRepository(Provider::class)->find($id);
        if (is_null($provider)) {

            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado al proveedor',
            ]);
        }

        $data = [
            'id'            => $provider->getId(),
            'name'          => $provider->getName(),
            'email'         => $provider->getEmail(),
            'status'        => $provider->getStatus(),
            'phoneNumber'   => $provider->getPhoneNumber(),
            'uniqueId'      => $provider->getUniqueIdentifier(),
        ];

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => $data,
        ]);
    }

    /**
     * @Route("/", name="providers.create", methods={"POST"})
     *
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createAction(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        Request $request
    ) {
        $name = $request->get('name');
        $phoneNumber = $request->get('phoneNumber');
        $uniqueId = $request->get('uniqueId');
        $email = $request->get('email');

        /** @var Provider $provider */
        $provider = new Provider();
        $provider
            ->setName($name)
            ->setPhoneNumber($phoneNumber)
            ->setUniqueIdentifier($uniqueId)
            ->setEmail($email)
        ;

        $validations = $validator->validate($provider);

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

        try {
            $em->persist($provider);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            $logger->error($e->getMessage());
            $logger->error($e->getTraceAsString());

            return $this->json([
                'code'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => 'El email o el identificador único ya están en uso por otro proveedor',
            ]);
        }

        return $this->json([
            'code'  => Response::HTTP_CREATED,
            'data'  => [
                'id'    => $provider->getId(),
                'createdAt' => $provider->getCreatedAt()->format('Y-m-d H:i:s')
            ],
        ]);
    }

    /**
     * @Route("/{provider}", name="providers.update", methods={"PUT"})
     *
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     * @param Request $request
     * @param $provider
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        Request $request,
        $provider
    ) {
        /** @var Provider $provider */
        $provider = $em->getRepository(Provider::class)->find($provider);
        if (is_null($provider)) {

            return $this->json([
                'code'  => Response::HTTP_OK,
                'error' => 'No se ha encontrado el proveedor'
            ]);
        }

        $name = $request->get('name');
        $phoneNumber = $request->get('phoneNumber');
        $uniqueId = $request->get('uniqueId');
        $email = $request->get('email');

        $provider
            ->setName($name)
            ->setPhoneNumber($phoneNumber)
        ;
        if ($provider->getEmail() !== $email) {
            $provider->setEmail($email);
        }
        if ($provider->getUniqueIdentifier() !== $uniqueId) {
            $provider->setUniqueIdentifier($uniqueId);
        }


        $validations = $validator->validate($provider);

        if (0 < count($validations)) {
            $errors = [];
            foreach ($validations as $validation) {
                $errors[] = $validation->getMessage();
            }

            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => $errors
            ]);
        }

        try {
            $em->persist($provider);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            $logger->error($e->getMessage());
            $logger->error($e->getTraceAsString());

            return $this->json([
                'code'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => 'El email o el identificador único ya están siendo usados por otro proveedor'
            ]);
        }

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => 'Ok',
        ]);
    }

    /**
     * @Route("/{provider]", name="providers.delete", methods={"DELETE"})
     *
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     * @param $provider
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction(EntityManagerInterface $em, LoggerInterface $logger, $provider)
    {
        /** @var Provider $provider */
        $provider = $em->getRepository(Provider::class)->find($provider);
        if (is_null($provider)) {

            return $this->json([
                'code'  => Response::HTTP_NOT_FOUND,
                'error' => 'No se ha encontrado el proveedor',
            ]);
        }

        $provider->setStatus(0);
        $em->persist($provider);
        $em->flush();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => 'Ok',
        ]);
    }
}