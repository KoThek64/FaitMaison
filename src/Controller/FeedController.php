<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RecipeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FeedController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/fil', name: 'app_feed')]
    public function index(Request $request, RecipeRepository $recipeRepository, PaginatorInterface $paginator): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $pagination = $paginator->paginate(
            $recipeRepository->findByFollowingQuery($currentUser),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('feed/index.html.twig', ['pagination' => $pagination]);
    }
}
