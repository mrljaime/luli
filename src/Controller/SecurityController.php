<?php
/**
 * @author José Jaime Ramírez Calvo <mr.ljaime@gmail.com>
 */

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends BaseController
{
    /**
     * @Route("/lucdlc", name="login.user", methods={"POST"})
     *
     * @param EntityManagerInterface $em
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function createUser(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $encoder,
        Request $request
    ) {
        /** @var User $user */
        $user = new User();

        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $password = $data['password'];
        $name = $data['name'];
        $lastname = $data['lastname'];

        $user
            ->setEmail($email)
            ->setPassword($encoder->encodePassword($user, $password))
            ->setName($name)
            ->setLastname($lastname)
        ;

        $errors = $validator->validate($user);

        if (0 < count($errors)) {
            $stuff = [];
            foreach ($errors as $error) {
                $stuff[] = $error->getMessage();
            }

            return $this->json([
                'code'  => Response::HTTP_BAD_REQUEST,
                'error' => $stuff
            ]);
        }

        // Validate that user doesn't exists yet
        if (!is_null($em->getRepository(User::class)->findOneBy(['email' => $email]))) {

            return $this->json([
                'code'  => Response::HTTP_IM_USED,
                'error' => 'Ya tenemos un usuario con ese email'
            ]);
        }

        $em->persist($user);
        $em->flush();

        return $this->json([
            'code'  => Response::HTTP_CREATED,
            'data'  => 'El usuario ha sido creado con éxito'
        ]);
    }

    /**
     * @Route("/loginCheck", name="login.check", methods={"POST"})
     *
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Exception
     */
    public function loginCheck(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['_email'];
        $password = $data['_password'];
        /** @var User $user */
        $user = $em->getRepository(User::class)->findOneBy([
            "email" => $email,
        ]);

        if (is_null($user)) {

            return $this->json([
                'code'  => Response::HTTP_OK,
                'error' => "No se ha encontrado al usuario"
            ]);
        }

        if (!$encoder->isPasswordValid($user, $password)) {

            return $this->json([
                'code'  => Response::HTTP_OK,
                'error' => 'La contraseña no es correcta'
            ]);
        }

        $now = new \DateTime("now", new \DateTimeZone("America/Mexico_City"));
        $now->add(\DateInterval::createFromDateString('+1 day'));

        $tokens = [
            'id'        => $user->getId(),
            'email'     => $user->getEmail(),
            'expire'    => $now->format("Y-m-d H:i:s")
        ];

        $token = JWT::encode($tokens, getenv('TOKENS_KEY'));

        $user
            ->setApiToken($token)
            ->setApiTokenExpiration($now)
        ;

        $em->persist($user);
        $em->flush();

        return $this->json([
            'code'  => Response::HTTP_OK,
            'data'  => [
                'id'        => $user->getId(),
                'name'      => $user->getName(),
                'lastname'  => $user->getLastname(),
                'apiToken'  => $user->getApiToken(),
            ],
        ]);
    }
}