<?php declare(strict_types=1);

namespace App\UserAccount;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

final class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByName(string $name): ?User
    {
        return $this->createQueryBuilder('user')
            ->select('user')
            ->where('user.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByToken(string $tokenValue): ?User
    {
        return $this->createQueryBuilder('user')
            ->select('user')
            ->leftJoin('user.authenticationToken', 'authenticationToken')
            ->where('authenticationToken.value = :tokenValue')
            ->setParameter('tokenValue', $tokenValue)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
