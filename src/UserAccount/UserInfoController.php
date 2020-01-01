<?php declare(strict_types=1);

namespace App\UserAccount;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

final class UserInfoController
{
    private UserRepository $userRepository;
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    public function __construct(UserRepository $userRepository, SerializerInterface $serializer)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request)
    {
        $tokenValue = $request->headers->get('X-AUTH-TOKEN');

        $user = $this->userRepository->findByToken($tokenValue);

        if (!$user) {
            throw new NotFoundHttpException('users.not_found');
        }

        return new Response(
            $this->serializer->serialize($user, 'json', ['groups' => ['user_private']]),
            Response::HTTP_OK
        );
    }
}
