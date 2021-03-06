<?php declare(strict_types=1);

namespace App\Chat;

use App\Game\Game;
use App\UserAccount\User;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Chat\MessageRepository")
 * @ORM\Table(name="messages")
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"message"})
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups({"message"})
     */
    private string $content;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups({"message"})
     */
    private bool $isGenerated;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserAccount\User")
     *
     * @Groups({"message"})
     */
    private User $emitter;

    /**
     * @ORM\Column(type="datetime_immutable")
     *
     * @Groups({"message"})
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Game\Game", inversedBy="messages")
     */
    private Game $game;

    public function __construct(User $emitter, Game $game, string $content, bool $isGenerated = false)
    {
        $this->emitter = $emitter;
        $this->game = $game;
        $this->content = $content;
        $this->isGenerated = $isGenerated;

        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
    }

    public function id(): ?UuidInterface
    {
        return $this->id;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function emitter(): User
    {
        return $this->emitter;
    }

    public function game(): Game
    {
        return $this->game;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isGenerated(): bool
    {
        return $this->isGenerated;
    }
}
