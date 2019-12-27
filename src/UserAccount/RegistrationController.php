<?php declare(strict_types=1);

namespace App\UserAccount;

use App\Infrastructure\Symfony\Exception\DomainException;
use App\UserAccount\Token\AuthenticationTokenType;
use App\UserAccount\Token\Token;
use Assert\Assert;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request): Response
    {
        $this->validateRequest($request);

        $user = User::register(
            $request->request->get('password'),
            $request->request->get('name'),
            $this->passwordEncoder
        );
        $this->entityManager->persist($user);

        $authenticationToken = Token::generateFor($user, new AuthenticationTokenType());
        $this->entityManager->persist($authenticationToken);

        $user->setAuthenticationToken($authenticationToken);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            throw new DomainException('users.already_used');
        }

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
