<?php declare(strict_types=1);

namespace App\UserAccount;

use Assert\Assert;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class RegistrationController
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private UserService $userService;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        UserService $userService
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->userService = $userService;
    }

    public function __invoke(Request $request): Response
    {
        $this->validateRequest($request);

        $user = $this->userService->register($request->request->get('name'), $request->request->get('password'));

        return new Response(
            $this->serializer->serialize($user, 'json', ['groups' => 'user_private']),
            Response::HTTP_CREATED
        );
    }

    private function validateRequest(Request $request): void
    {
        Assert::that($request->request->get('password'), null, 'password')->notNull('data_control.users.is_null.password');
        Assert::that($request->request->get('name'), null, 'name')->notNull('data_control.users.is_null.name');

        Assert::lazy()
            ->that($request->request->get('password'), 'password')
            ->string('data_control.users.is_not_string.password')
            ->notBlank('data_control.users.is_blank.password')

            ->that($request->request->get('name'), 'name')
            ->string('data_control.users.is_not_string.name')
            ->notBlank('data_control.users.is_blank.name')

            ->verifyNow();
    }
}
