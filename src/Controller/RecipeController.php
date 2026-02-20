<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RecipeController extends AbstractController
{
    #[Route('/recettes', name: 'app_recipe')]
    public function index(RecipeRepository $recipes): Response
    {
        $all = $recipes->findAll();
        return $this->render('recipe/index.html.twig', ['recipes' => $all]);
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
    public function show(Recipe $recipe) : Response
    {
        return $this->render('recipe/show.html.twig', ['recipe' => $recipe]);
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
