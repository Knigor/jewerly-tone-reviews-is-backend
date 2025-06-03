<?php

namespace App\Controller\Auth;

use App\Dto\RegisterUserDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        try {
            /** @var RegisterUserDto $registerDto */
            $registerDto = $serializer->deserialize(
                $request->getContent(),
                RegisterUserDto::class,
                'json'
            );

            $errors = $validator->validate($registerDto);
            if (count($errors) > 0) {
                return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
            }

            // Проверка на существующего пользователя
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $registerDto->email]);
            if ($existingUser) {
                return $this->json(['message' => 'Email already registered'], Response::HTTP_CONFLICT);
            }

            // Создание нового пользователя
            $user = new User();
            $user
                ->setFullName($registerDto->fullName)
                ->setEmail($registerDto->email)
                ->setUserAboba($registerDto->username)
                ->setPasswordHash($passwordHasher->hashPassword($user, $registerDto->password))
                ->setRole('ROLE_USER');

            $em->persist($user);
            $em->flush();

            // Генерация access_token
            $accessToken = $jwtManager->create($user);


            // Генерация refresh_token
            $refreshTokenPayload = [
                'username' => $user->getUserIdentifier(),
                'exp' => time() + 60 * 60 * 24 * 7 // 7 дней
            ];

            $refreshToken = base64_encode(json_encode($refreshTokenPayload));


            // Установка refresh_token в HttpOnly cookie
            $refreshTokenCookie = Cookie::create('refresh_token')
                ->withValue($refreshToken)
                ->withHttpOnly(true)
                ->withSameSite('lax')
                ->withSecure(false)
                ->withPath('/')
                ->withExpires(strtotime('+7 days'));

            // Ответ с токеном и данными пользователя
            $response = new JsonResponse([
                'access_token' => $accessToken,
                'user' => [
                    'id' => $user->getId(),
                    'name' => $user->getFullName(),
                    'username' => $user->getUserAboba(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole(),
                ]
            ], Response::HTTP_CREATED);

            $response->headers->setCookie($refreshTokenCookie);

            return $response;
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}