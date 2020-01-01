<?php declare(strict_types=1);

namespace App\Game;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

final class ListGamesController extends AbstractController
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

    public function __invoke(): Response
    {
        $games = $this->gameRepository->findAll();

        return new Response($this->serializer->serialize($games, 'json', ['groups' => 'game']));
    }
}
