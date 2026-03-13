<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByAdminFilters(array $filters = []): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC');

        if (!empty($filters['search'])) {
            $qb->andWhere('u.username LIKE :search OR u.email LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'banned') {
                $qb->andWhere('u.bannedUntil IS NOT NULL')
                   ->andWhere('u.bannedUntil > :now')
                   ->setParameter('now', new \DateTimeImmutable());
            } elseif ($filters['status'] === 'active') {
                $qb->andWhere('u.bannedUntil IS NULL OR u.bannedUntil <= :now')
                   ->setParameter('now', new \DateTimeImmutable());
            }
        }

        return $qb->getQuery();
    }

    public function findUserByDesc()
    {
        $qb = $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC');

        return $qb->getQuery();
    }

    public function findBannedUsers()
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.bannedUntil IS NOT NULL')
            ->andWhere('u.bannedUntil > :now')
            ->setParameter('now', new \DateTimeImmutable());

        return $qb->getQuery()->getResult();
    }
}
