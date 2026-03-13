<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeFilterType;
use App\Form\RecipeType;
use App\Repository\FavoriteRepository;
use App\Repository\LikeRepository;
use App\Repository\RatingRepository;
use App\Repository\RecipeRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RecipeController extends AbstractController
{
    #[Route('/recettes', name: 'app_recipe', methods: ['GET'])]
    public function index(RecipeRepository $recipes, Request $request, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(RecipeFilterType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        $data = $form->isSubmitted() ? $form->getData() : [];

        $pagination = $paginator->paginate(
            $recipes->findPublishedQuery($data),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('recipe/index.html.twig', ['pagination' => $pagination, 'form' => $form]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/recette/ajouter', name: 'app_recipe_add')]
    public function new(Request $request, EntityManagerInterface $em, ImageUploader $imageUploader): Response
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe->setAuthor($this->getUser());
            $recipe->setCreatedAt(new \DateTimeImmutable());
            $recipe->setIsPublished($request->request->get('action') === 'publish');

            /** @var UploadedFile|null $fichier */
            $fichier = $form->get('imageFile')->getData();
            if ($fichier) {
                $recipe->setImageName($imageUploader->upload($fichier));
            }

            $em->persist($recipe);
            $em->flush();

            $this->addFlash(
                'success',
                $recipe->isPublished() ? 'Recette publiée avec succès !' : 'Recette enregistrée en brouillon.'
            );

            return $this->redirectToRoute('app_recipe');
        }

        return $this->render('recipe/new.html.twig', ['form' => $form]);
    }

    #[Route('/recette/{id}', name: 'app_recipe_show', requirements: ['id' => '\d+'])]
    public function show(Recipe $recipe, FavoriteRepository $favoriteRepository, RatingRepository $ratingRepository, LikeRepository $likeRepository): Response
    {
        $isFavorite = false;
        $userRating = null;
        $isLiked = false;

        if ($this->getUser()) {
            $isFavorite = (bool) $favoriteRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe,
            ]);
            $userRating = $ratingRepository->findOneBy([
                'user' => $this->getUser(),
                'recipe' => $recipe,
            ]);
            $isLiked = (bool) $likeRepository->findOneBy([
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
            'isLiked' => $isLiked,
        ]);
    }

    #[Route('/recette/{id}/modifier', name: 'app_recipe_edit', methods: ['GET', 'POST'])]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em, ImageUploader $imageUploader): Response
    {
        if ($this->getUser() !== $recipe->getAuthor()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recipe->setUpdatedAt(new \DateTime());

            $wantsToPublish = $request->request->get('action') === 'publish';
            if ($wantsToPublish && $recipe->getAdminNote()) {
                return $this->redirectToRoute('app_recipe_edit', ['id' => $recipe->getId()]);
            }
            $recipe->setIsPublished($wantsToPublish);

            /** @var UploadedFile|null $fichier */
            $fichier = $form->get('imageFile')->getData();
            if ($fichier) {
                if ($recipe->getImageName()) {
                    $imageUploader->delete($recipe->getImageName());
                }
                $recipe->setImageName($imageUploader->upload($fichier));
            }

            $em->flush();

            if ($recipe->getAdminNote()) {
                $this->addFlash('success', 'Modifications enregistrées.');
            } else {
                $this->addFlash(
                    'success',
                    $recipe->isPublished() ? 'Recette publiée avec succès !' : 'Recette repassée en brouillon.'
                );
            }

            return $this->redirectToRoute('app_recipe_show', ['id' => $recipe->getId()]);
        }

        return $this->render('recipe/edit.html.twig', ['recipe' => $recipe, 'form' => $form]);
    }

    #[Route('/recette/{id}/supprimer', name: 'app_recipe_delete', methods: ['POST'])]
    public function delete(Recipe $recipe, EntityManagerInterface $em, ImageUploader $imageUploader): Response
    {
        if ($this->getUser() !== $recipe->getAuthor()) {
            throw $this->createAccessDeniedException();
        }

        if ($recipe->getImageName()) {
            $imageUploader->delete($recipe->getImageName());
        }

        $em->remove($recipe);
        $em->flush();
        return $this->redirectToRoute('app_recipe');
    }
}
