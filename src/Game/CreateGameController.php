<?php declare(strict_types=1);

namespace App\Game;

use App\Infrastructure\Symfony\Exception\DomainException;
use Assert\Assert;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

final class CreateGameController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request)
    {
        $this->validateRequest($request);

        $gameMaster = $this->getUser();

        $game = Game::create($request->request->get('title'), $gameMaster);

        try {
            $this->entityManager->persist($game);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            throw new DomainException('games.title.already_used');
        }

        return new Response($this->serializer->serialize($game, 'json', ['groups' => 'game']));
    }

    private function validateRequest(Request $request): void
    {
        Assert::that($request->request->get('title'), null, 'title')->notNull('data_control.games.is_null.title');
        Assert::lazy()
            ->that($request->request->get('title'), 'title')
            ->string('data_control.games.is_not_string.title')
            ->notBlank('data_control.games.is_blank.title')
            ->verifyNow();
    }
}
