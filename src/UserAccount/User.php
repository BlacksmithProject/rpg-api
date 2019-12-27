<?php declare(strict_types=1);

namespace App\UserAccount;

use App\UserAccount\Token\Token;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\UserAccount\UserRepository")
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"user_private", "user_public"})
     */
    private UuidInterface $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Groups({"user_private", "user_public"})
     */
    private string $name;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeImmutable $updatedAt;

    /**
     * @ORM\OneToOne(targetEntity="App\UserAccount\Token\Token", mappedBy="user")
     *
     * @Groups({"user_private"})
     */
    private Token $authenticationToken;

    public static function register(
        string $password,
        string $username,
        UserPasswordEncoderInterface $passwordEncoder
    ): self {
        $user = new self();
        $now = new \DateTimeImmutable();

        $user->password = $passwordEncoder->encodePassword($user, $password);
        $user->name = $username;
        $user->createdAt = $now;
        $user->updatedAt = $now;

        return $user;
    }

    public function id(): UuidInterface
    {
        return $this->id;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function authenticationToken(): Token
    {
        return $this->authenticationToken;
    }

    public function setAuthenticationToken(Token $authenticationToken): User
    {
        $this->authenticationToken = $authenticationToken;

        return $this;
    }

    public function getRoles()
    {
        return [];
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->name;
    }

    public function eraseCredentials(): void
    {
    }
}
