<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Comment;
use App\Entity\Recipe;
use App\Entity\User;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin_dashboard')]
    public function dashboard(UserRepository $userRepository, RecipeRepository $recipeRepository, CommentRepository $commentRepository) : Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'userCount' => $userRepository->count([]),
            'recipeCount' => $recipeRepository->count([]),
            'commentCount' => $commentRepository->count([]),
            'recentRecipes' => $recipeRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'recentUsers' => $userRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'recentComments' => $commentRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'unpublishedRecipes' => $recipeRepository->findAdminUnpublished(),
            'bannedUsers' => $userRepository->findBannedUsers()
        ]);
    }

    #[Route('/admin/recettes', name: 'app_admin_recipe')]
    public function recipes(RecipeRepository $recipeRepository, Request $request): Response
    {
        $filters = [
            'search' => $request->query->get('search'),
            'status' => $request->query->get('status'),
        ];

        return $this->render('admin/recipes.html.twig', [
            'recipes' => $recipeRepository->findByAdminFilters($filters),
            'filters' => $filters,
        ]);
    }

    #[Route('/admin/commentaires', name: 'app_admin_comments')]
    public function comments(CommentRepository $commentRepository): Response
    {
        return $this->render('admin/comments.html.twig', [
            'comments' => $commentRepository->findBy([], ['createdAt' => 'DESC'])
        ]);
    }

    #[Route('/admin/utilisateurs', name: 'app_admin_users')]
    public function users(PaginatorInterface $paginator, UserRepository $userRepository, Request $request): Response
    {
        $filters = [
            'search' => $request->query->get('search'),
            'status' => $request->query->get('status'),
        ];

        $pagination = $paginator->paginate(
            $userRepository->findByAdminFilters($filters),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('admin/users.html.twig', [
            'pagination' => $pagination,
            'filters' => $filters,
        ]);
    }

    #[Route('/admin/recette/{id}/depublier', name: 'app_admin_recipe_unpublish')]
    public function depublier(RecipeRepository $recipeRepository, int $id, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $recipe = $recipeRepository->find($id);

        $recipe->setIsPublished(false);
        $recipe->setAdminNote($request->request->get('note'));

        $em->persist($recipe);
        $em->flush();
        return $this->redirectToRoute('app_admin_recipe');
    }

    #[Route('/admin/recette/{id}/republier', name: 'app_admin_recipe_republish')]
    public function republier(RecipeRepository $recipeRepository, int $id, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        $recipe = $recipeRepository->find($id);

        $recipe->setIsPublished(true);
        $recipe->setAdminNote(null);

        $em->persist($recipe);
        $em->flush();
        return $this->redirectToRoute('app_admin_recipe');
    }

    #[Route('/admin/recette/{id}/supprimer', name: 'app_admin_recipe_delete')]
    public function deleteRecipe(Recipe $recipe, EntityManagerInterface $em, ImageUploader $imageUploader): RedirectResponse
    {
        if ($recipe->getImageName()) {
            $imageUploader->delete($recipe->getImageName());
        }

        $em->remove($recipe);
        $em->flush();
        return $this->redirectToRoute('app_admin_recipe');
    }

    #[Route('/admin/commentaire/{id}/supprimer', name: 'app_admin_comment_delete')]
    public function deleteComment(EntityManagerInterface $em, Comment $comment): RedirectResponse
    {
        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute('app_admin_comments');
    }

    #[Route('/admin/utilisateur/{id}/supprimer', name: 'app_admin_user_delete')]
    public function deleteUser(EntityManagerInterface $em, User $user, ImageUploader $imageUploader): RedirectResponse
    {
        if ($user->getAvatarName()) {
            $imageUploader->delete($user->getAvatarName());
        }

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/admin/utilisateur/{id}/bannir', name: 'app_admin_user_ban')]
    public function bannir(Request $request, User $user, EntityManagerInterface $em): RedirectResponse
    {
        if ($user === $this->getUser()) {
            return $this->redirectToRoute('app_admin_users');
        }

        $duration = (int)$request->request->get('duration');
        $reason = $request->request->get('reason');

        $dateFin = new \DateTimeImmutable('+' . $duration . ' days');

        $user->setBannedUntil($dateFin);
        $user->setBanReason($reason);

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/admin/utilisateur/{id}/debannir', name: 'app_admin_user_unban')]
    public function debannir(EntityManagerInterface $em, User $user): RedirectResponse
    {
        $user->setBanReason(null);
        $user->setBannedUntil(null);

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_admin_users');
    }
}
