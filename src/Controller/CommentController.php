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

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'id'               => $comment->getId(),
                'content'          => $comment->getContent(),
                'authorId'         => $comment->getAuthor()->getId(),
                'authorUsername'   => $comment->getAuthor()->getUsername(),
                'authorAvatarName' => $comment->getAuthor()->getAvatarName(),
                'createdAt'        => $comment->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('d/m/Y à H:i'),
            ]);
        }

        return $this->redirectToRoute('app_recipe_show' , ['id' => $recipe->getId()]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/commentaire/{id}/supprimer', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $em)
    {
        $currentUser = $this->getUser();
        if ($comment->getAuthor() !== $currentUser){
            throw $this->createAccessDeniedException();
        }

        $recipeId = $comment->getRecipe()->getId();
        $em->remove($comment);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => true]);
        }

        return $this->redirectToRoute('app_recipe_show', ['id' => $recipeId]);
    }
}
