<?php declare(strict_types=1);

namespace App\UserAccount;

use App\Infrastructure\Symfony\Exception\DomainException;
use App\UserAccount\Token\AuthenticationTokenType;
use App\UserAccount\Token\Token;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class UserService
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
    }

    public function register(string $name, string $password): User
    {
        $user = User::register($password, $name, $this->passwordEncoder);
        $this->entityManager->persist($user);

        $authenticationToken = Token::generateFor($user, new AuthenticationTokenType());
        $this->entityManager->persist($authenticationToken);

        $user->setAuthenticationToken($authenticationToken);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            throw new DomainException('users.already_used');
        }

        return $user;
    }
}
