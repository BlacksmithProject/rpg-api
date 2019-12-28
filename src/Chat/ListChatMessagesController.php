<?php declare(strict_types=1);

namespace App\Chat;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

final class ListChatMessagesController
{
    private SerializerInterface $serializer;
    private MessageRepository $messageRepository;

    public function __construct(
        SerializerInterface $serializer,
        MessageRepository $messageRepository
    ) {
        $this->serializer = $serializer;
        $this->messageRepository = $messageRepository;
    }

    public function __invoke()
    {
        $messages = $this->messageRepository->findBy([], ['createdAt' => 'ASC']);

        return new Response(
            $this->serializer->serialize($messages, 'json', ['groups' => 'message']),
            Response::HTTP_OK
        );
    }
}
