<?php declare(strict_types=1);

namespace App\Chat;

use App\Game\GameRepository;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

final class ListChatMessagesController
{
    private SerializerInterface $serializer;
    private GameRepository $gameRepository;
    private MessageRepository $messageRepository;

    public function __construct(
        SerializerInterface $serializer,
        GameRepository $gameRepository,
        MessageRepository $messageRepository
    ) {
        $this->serializer = $serializer;
        $this->gameRepository = $gameRepository;
        $this->messageRepository = $messageRepository;
    }

    public function __invoke(string $gameId)
    {
        $game = $this->gameRepository->find(Uuid::fromString($gameId));

        if (!$game) {
            throw new NotFoundHttpException('games.not_found');
        }

        $messages = $this->messageRepository->findBy(['game' => $game], ['createdAt' => 'ASC']);

        return new Response(
            $this->serializer->serialize($messages, 'json', ['groups' => 'message']),
            Response::HTTP_OK
        );
    }
}
