<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeFilterType;
use App\Form\RecipeType;
use App\Repository\FavoriteRepository;
use App\Repository\RatingRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RecipeController extends AbstractController
{
    #[Route('/recettes', name: 'app_recipe', methods: ['GET'])]
    public function index(RecipeRepository $recipes, Request $request): Response
    {
        $form = $this->createForm(RecipeFilterType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        $data = $form->isSubmitted() ? $form->getData() : [];

        $all = $recipes->findPublished($data);
        return $this->render('recipe/index.html.twig', ['recipes' => $all, 'form' => $form]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/recette/ajouter', name: 'app_recipe_add')]
    public function new(Request $request, EntityManagerInterface $em) : Response
    {
        $recipe = new Recipe;
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $recipe->setAuthor($this->getUser());
            $recipe->setCreatedAt(new \DateTimeImmutable());

            $em->persist($recipe);
            $em->flush();

            return $this->redirectToRoute('app_recipe');
        }

        return $this->render('recipe/new.html.twig', ['form' => $form]);
    }

    #[Route('/recette/{id}', name: 'app_recipe_show')]
    public function show(Recipe $recipe, FavoriteRepository $favoriteRepository, RatingRepository $ratingRepository) : Response
    {
        $isFavorite = false;
        $userRating = null;

        if ($this->getUser()) {
            $isFavorite = (bool) $favoriteRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe,
            ]);
            $userRating = $ratingRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe,
            ]);
        }

        $averageRating = $ratingRepository->findAverageForRecipe($recipe);

        return $this->render('recipe/show.html.twig', [
            'recipe' => $recipe,
            'isFavorite' => $isFavorite,
            'userRating' => $userRating,
            'average' => $averageRating,
        ]);
    }

    #[Route('/recette/{id}/modifier', name: 'app_recipe_edit', methods: ['GET', 'POST'])]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em) : Response {
        if ($this->getUser() !== $recipe->getAuthor()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $recipe->setUpdatedAt(new \DateTime());

            $em->persist($recipe);
            $em->flush();

            return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
        }

        return $this->render('recipe/edit.html.twig', ['recipe' => $recipe, 'form' => $form]);
    }

    #[Route('/recette/{id}/supprimer', name: 'app_recipe_delete', methods: ['POST'])]
    public function delete(Recipe $recipe, EntityManagerInterface $em) : Response{
        if ($this->getUser() !== $recipe->getAuthor()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($recipe);
        $em->flush();
        return $this->redirectToRoute('app_recipe');
    }
}
