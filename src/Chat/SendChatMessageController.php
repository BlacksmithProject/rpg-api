<?php declare(strict_types=1);

namespace App\Chat;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Serializer\SerializerInterface;

final class SendChatMessageController extends AbstractController
{
    private PublisherInterface $publisher;
    private SerializerInterface $serializer;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PublisherInterface $publisher,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager
    ) {
        $this->publisher = $publisher;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    public function __invoke(Request $request)
    {
        $user = $this->getUser();
        $content = $request->request->get('message');

        $message = new Message($user, $content);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $response = 'aborted';

        if ($message !== '') {
            $update = new Update(
                'chat/messages',
                $this->serializer->serialize($message, 'json', ['groups' => 'message']),
            );

            ($this->publisher)($update);

            $response = 'published';
        }

        return new Response($response);
    }
}
