<?php declare(strict_types=1);

namespace App\Game;

use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

final class GameInfoController extends AbstractController
{
    private GameRepository $gameRepository;
    private SerializerInterface $serializer;

    public function __construct(
        GameRepository $gameRepository,
        SerializerInterface $serializer
    ) {
        $this->gameRepository = $gameRepository;
        $this->serializer = $serializer;
    }

    public function __invoke(string $gameId): Response
    {
        /** @var Game $game */
        $game = $this->gameRepository->find(Uuid::fromString($gameId));

        if (!$game) {
            throw new NotFoundHttpException('games.not_found');
        }
        $group = 'game';

        $user = $this->getUser();

        if ($game->gameMaster() === $user) {
            $group = 'game_owned';
        }

        return new Response($this->serializer->serialize($game, 'json', ['groups' => $group]));
    }
}
