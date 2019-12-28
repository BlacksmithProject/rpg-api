<?php declare(strict_types=1);

namespace App\Infrastructure\Symfony;

use App\Chat\Message;
use App\UserAccount\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FixturesCommand extends Command
{
    private UserService $userService;
    private EntityManagerInterface $entityManager;

    public function __construct(
        UserService $userService,
        EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
        $this->userService = $userService;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setName('fixtures:create');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $password = 'aze';

        $usersData = [
            [
                'name' => 'Console',
                'password' => $password,
            ],
            [
                'name' => 'John',
                'password' => $password,
            ],
            [
                'name' => 'Jane',
                'password' => $password,
            ],
        ];

        $users = [];

        foreach ($usersData as $userData) {
            $user = $this->userService->register($userData['name'], $userData['password']);
            $users[$user->name()] = $user;
            $output->writeln(sprintf('User "%s" has been registered', $user->name()));
        }

        $messages = [
            [
                'emitter' => 'John',
                'content' => 'Hello Jane !',
                'isGenerated' => false,
            ],
            [
                'emitter' => 'Jane',
                'content' => 'Hi John ! How are you ?',
                'isGenerated' => false,
            ],
            [
                'emitter' => 'John',
                'content' => "I'm fine ! And you ?",
                'isGenerated' => false,
            ],
            [
                'emitter' => 'Jane',
                'content' => 'Pretty good, thank you.',
                'isGenerated' => false,
            ],
        ];

        for ($i = 0; $i < 15; $i++) {
            $player = (rand(0, 1) === 0) ? $users['John'] : $users['Jane'];
            $diceRoll = rand(1, 100);
            $message = sprintf('%s made a roll and obtained %d.', $player->name(), $diceRoll);

            $messages[] = [
                'emitter' => $player->name(),
                'content' => $message,
                'isGenerated' => true,
            ];
        }

        foreach ($messages as $messageData) {
            $message = new Message($users[$messageData['emitter']], $messageData['content'], $messageData['isGenerated']);
            sleep(1);
            $this->entityManager->persist($message);
            $this->entityManager->flush();
            $output->writeln(sprintf('Message "%s" has been registered', $message->content()));
        }

        return 0;
    }
}
