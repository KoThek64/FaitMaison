<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Recipe;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CommentController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('recette/{id}/commenter', name: 'app_comment_add', methods: ['POST'])]
    public function add(Request $request, Recipe $recipe, EntityManagerInterface $em)
    {
        $content = $request->request->get('content');
        if ($content === "" || $content === null){
            return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
        }
        $comment = new Comment();
        $comment->setRecipe($recipe);
        $comment->setAuthor($this->getUser());
        $comment->setContent($content);

        $em->persist($comment);
        $em->flush();
        return $this->redirectToRoute('app_recipe_show' , ['id' => $recipe->getId()]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/commentaire/{id}/supprimer', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Comment $comment, EntityManagerInterface $em)
    {
        $currentUser = $this->getUser();
        if ($comment->getAuthor() !== $currentUser){
            throw $this->createAccessDeniedException();
        }

        $recipeId = $comment->getRecipe()->getId();
        $em->remove($comment);
        $em->flush();
        return $this->redirectToRoute('app_recipe_show', ['id' => $recipeId]);
    }
}
