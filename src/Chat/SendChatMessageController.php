<?php declare(strict_types=1);

namespace App\Chat;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;

final class SendChatMessageController extends AbstractController
{
    /**
     * @var PublisherInterface
     */
    private PublisherInterface $publisher;

    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    public function __invoke(Request $request)
    {
        $user = $this->getUser();
        $message = $request->request->get('message');

        $response = 'aborted';

        if ($message !== '') {
            $update = new Update(
                'chat/messages',
                json_encode([
                    'username' => $user->name(),
                    'message' => $message,
                ]),
            );

            ($this->publisher)($update);

            $response = 'published';
        }


        return new Response($response);
    }
}
