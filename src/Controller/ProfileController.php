<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ProfileController extends AbstractController
{
    #[Route('/profil/{id}', name: 'app_profile_show')]
    public function show(User $user): Response
    {
        return $this->render('profile/show.html.twig', ['user' => $user]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/profil/modifier', name: 'app_profile_edit', methods: ['GET','POST'])]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher)
    {
        $user = $this->getUser();
        if (!$user instanceof User){
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $password = $form->get('plainPassword')->getData();
            if ($password != null){
                $passwordHash = $hasher->hashPassword($user, $password);
                $user->setPassword($passwordHash);
            }

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_profile_show', ['id' => $user->getId()]);
        }

        return $this->render('profile/edit.html.twig', ['user' => $user, 'form' => $form]);
    }
}
