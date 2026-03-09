<?php

namespace App\Repository;

use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rating>
 */
class RatingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rating::class);
    }

    public function findAverageForRecipe(Recipe $recipe)
    {
        $average = $this->createQueryBuilder('r')
            ->select('AVG(r.score)')
            ->andWhere('r.recipe = :recipe')
            ->setParameter('recipe', $recipe)
            ->getQuery()->getSingleScalarResult();

        return round($average, 1);
    }

    public function findAverageForUser(User $user)
    {
        $average = $this->createQueryBuilder('r')
            ->select('AVG(r.score)')
            ->join('r.recipe', 'recipe')
            ->andWhere('recipe.author = :user')
            ->andWhere('recipe.isPublished = true')
            ->setParameter('user' , $user)
            ->getQuery()
            ->getSingleScalarResult();

        if ($average === null){
            return 0;
        }
        return round($average, 1);
    }
//    public function findOneBySomeField($value): ?Rating
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
