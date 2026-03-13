<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\Recipe;
use App\Entity\User;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;

final class LikeController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route("/recette/{id}/liker", name: 'app_like_toggle', methods: ['POST'])]
    public function toggle(Request $request, LikeRepository $likeRepository, EntityManagerInterface $em, Recipe $recipe)
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User){
            throw $this->createAccessDeniedException();
        }

        $like = $likeRepository->findOneBy(['user' => $currentUser, 'recipe' => $recipe]);
        if ($like){
            $em->remove($like);
        } else {
            $newLike = new Like();
            $newLike->setRecipe($recipe);
            $newLike->setUser($currentUser);
            $em->persist($newLike);
        }

        $em->flush();
        if ($request->isXmlHttpRequest()){
            return $this->json([
                "liked" => !isset($like),
                "count" => $likeRepository->count(['recipe' => $recipe])
            ]);
        } else {
            return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
        }

    }
}
