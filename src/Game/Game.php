<?php declare(strict_types=1);

namespace App\Game;

use App\UserAccount\User;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Game\GameRepository")
 * @ORM\Table(name="games")
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     *
     * @Groups({"game", "game_owned"})
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @Groups({"game", "game_owned"})
     */
    private string $title;

    /**
     * @ORM\ManyToOne(targetEntity="App\UserAccount\User", inversedBy="games")
     *
     * @Groups({"game_owned"})
     */
    private User $gameMaster;

    public function __construct()
    {
    }

    public static function create(string $title, User $gameMaster): self
    {
        $game = new self();

        $game->id = Uuid::uuid4();
        $game->title = $title;
        $game->gameMaster = $gameMaster;

        return $game;
    }

    public function id(): ?UuidInterface
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function gameMaster(): User
    {
        return $this->gameMaster;
    }
}
