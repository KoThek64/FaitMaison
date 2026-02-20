<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\RatingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RatingController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/recette/{id}/noter', name: 'app_recipe_rating', methods: ['POST'])]
    public function rate(Request $request, RatingRepository $ratingRepository, Recipe $recipe, EntityManagerInterface $em)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            throw $this->createAccessDeniedException();
        }

        $score = $request->request->get('score');
        if ($score < 1 || $score > 5){
            throw $this->createAccessDeniedException();
        }
        $dejaNote = $ratingRepository->findOneBy(['user' => $user, 'recipe' => $recipe]);
        if ($dejaNote){
            $dejaNote->setScore($score);
            $em->persist($dejaNote);
        } else {
            $nouveauScore = new Rating();
            $nouveauScore->setUser($user);
            $nouveauScore->setRecipe($recipe);
            $nouveauScore->setScore($score);
            $nouveauScore->setCreatedAt(new \DateTimeImmutable());

            $em->persist($nouveauScore);
        }
        $em->flush();

        return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
    }
}
