<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\FavoriteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FavoriteController extends AbstractController
{
    #[Route('/recette/{id}/favori', name: 'app_favorite_toggle', methods: ['POST'])]
    public function toggle(Recipe $recipe, EntityManagerInterface $em, FavoriteRepository $favoriteRepository){
        $user = $this->getUser();
        if(!$user instanceof User){
            throw $this->createAccessDeniedException();
        }

        $favori = $favoriteRepository->findOneBy(['user' => $user, 'recipe' => $recipe]);
        if ($favori){
            $em->remove($favori);
            $em->flush();
        } else {
            $nouveauFavori = new Favorite();
            $nouveauFavori->setUser($user);
            $nouveauFavori->setRecipe($recipe);
            $nouveauFavori->setCreatedAt(new \DateTimeImmutable());

            $em->persist($nouveauFavori);
            $em->flush();
        }

        return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
    }
}
