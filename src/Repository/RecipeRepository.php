<?php

namespace App\Repository;

use App\Entity\Follow;
use App\Entity\Like;
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

    public function findRecommended(User $user): array
    {
        // Sous-requête : catégories des recettes likées par l'utilisateur
        $likedCategoriesDql = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(r2.category)')
            ->from(Like::class, 'l2')
            ->join('l2.recipe', 'r2')
            ->where('l2.user = :user')
            ->getDQL();

        // Sous-requête : IDs des recettes déjà likées
        $likedRecipesDql = $this->getEntityManager()->createQueryBuilder()
            ->select('IDENTITY(l3.recipe)')
            ->from(Like::class, 'l3')
            ->where('l3.user = :user')
            ->getDQL();

        $results = $this->createQueryBuilder('r')
            ->where('r.isPublished = true')
            ->andWhere('r.author != :user')
            ->andWhere('IDENTITY(r.category) IN (' . $likedCategoriesDql . ')')
            ->andWhere('r.id NOT IN (' . $likedRecipesDql . ')')
            ->setParameter('user', $user)
            ->orderBy('SIZE(r.likes)', 'DESC')
            ->addOrderBy('r.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Fallback si aucun like : recettes récentes hors propres recettes
        if (empty($results)) {
            return $this->createQueryBuilder('r')
                ->where('r.isPublished = true')
                ->andWhere('r.author != :user')
                ->setParameter('user', $user)
                ->orderBy('SIZE(r.likes)', 'DESC')
                ->addOrderBy('r.createdAt', 'DESC')
                ->setMaxResults(10)
                ->getQuery()
                ->getResult();
        }

        return $results;
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
}
