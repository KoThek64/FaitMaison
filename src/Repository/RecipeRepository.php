<?php

namespace App\Repository;

use App\Entity\Follow;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Recipe>
 */
class RecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Recipe::class);
    }

    public function findPublished(array $filters = [], ?int $limit = null) {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.isPublished = true')
            ->orderBy('r.createdAt','DESC');

        if (!empty($filters['search'])){
            $qb->andWhere('r.title LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['difficulty'])){
            $qb->andWhere('r.difficulty = :difficulty')
                ->setParameter('difficulty', $filters['difficulty']);
        }

        if (!empty($filters['category'])){
            $qb->andWhere('r.category = :category')
                ->setParameter('category' , $filters['category']);
        }

        if (!empty($filters['maxDuration'])){
            $qb->andWhere('r.duration <= :maxDuration')
                ->setParameter('maxDuration' , $filters['maxDuration']);
        }

        if (!empty($filters['servings'])){
            $qb->andWhere('r.servings = :servings')
                ->setParameter('servings' , $filters['servings']);
        }

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findPublishedQuery(array $filters = []): \Doctrine\ORM\Query
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.isPublished = true')
            ->orderBy('r.createdAt', 'DESC');

        if (!empty($filters['search'])) {
            $qb->andWhere('r.title LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }
        if (!empty($filters['difficulty'])) {
            $qb->andWhere('r.difficulty = :difficulty')
                ->setParameter('difficulty', $filters['difficulty']);
        }
        if (!empty($filters['category'])) {
            $qb->andWhere('r.category = :category')
                ->setParameter('category', $filters['category']);
        }
        if (!empty($filters['maxDuration'])) {
            $qb->andWhere('r.duration <= :maxDuration')
                ->setParameter('maxDuration', $filters['maxDuration']);
        }
        if (!empty($filters['servings'])) {
            $qb->andWhere('r.servings = :servings')
                ->setParameter('servings', $filters['servings']);
        }

        return $qb->getQuery();
    }

    public function findByFollowingQuery(User $user): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('r')
            ->join(Follow::class, 'f', 'WITH', 'f.followed = r.author AND f.follower = :user')
            ->where('r.isPublished = true')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery();
    }

//    /**
//     * @return Recipe[] Returns an array of Recipe objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Recipe
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
