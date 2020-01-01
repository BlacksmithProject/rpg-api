<?php declare(strict_types=1);

namespace App\Chat;

use App\Game\Game;
use App\Game\GameRepository;
use Assert\Assert;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

final class SendChatMessageController extends AbstractController
{
    private GameRepository $gameRepository;
    private PublisherInterface $publisher;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;

    public function __construct(
        GameRepository $gameRepository,
        PublisherInterface $publisher,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    ) {
        $this->gameRepository = $gameRepository;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    public function __invoke(Request $request)
    {
        $user = $this->getUser();

        $this->validateRequest($request);

        $content = $request->request->get('message');
        $gameId = $request->request->get('gameId');

        /** @var Game $game */
        $game = $this->gameRepository->find(Uuid::fromString($gameId));

        if (!$game) {
            throw new NotFoundHttpException('games.not_found');
        }

        $message = new Message($user, $game, $content);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $update = new Update(
            'chat/messages/'.$game->id()->toString(),
            $this->serializer->serialize($message, 'json', ['groups' => 'message']),
        );

        ($this->publisher)($update);

        return new Response('published');
    }

    private function validateRequest(Request $request): void
    {
        Assert::that($request->request->get('message'), null, 'message')->notNull('data_control.message.is_null');
        Assert::that($request->request->get('gameId'), null, 'gameId')->notNull('data_control.games.is_null.id');
        Assert::lazy()

            ->that($request->request->get('message'), 'message')
            ->string('data_control.message.is_not_string')
            ->notBlank('data_control.message.is_blank')

            ->that($request->request->get('gameId'), 'gameId')
            ->uuid('data_control.games.is_not_uuid.id')
            ->notBlank('data_control.games.is_blank.id')

            ->verifyNow();
    }
}
