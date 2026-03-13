<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

final class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        VerifyEmailHelperInterface $emailHelper,
        MailerInterface $mailer
    )
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $password = $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($password);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setRoles([]);

            $em->persist($user);
            $em->flush();

            $generatedSignature = $emailHelper->generateSignature(
                'app_verify_email',
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            $email = (new TemplatedEmail())
                ->from('onboarding@resend.dev')
                ->to($user->getEmail())
                ->subject('Confirmez votre adresse email')
                ->htmlTemplate('emails/confirmation.html.twig')
                ->context([
                    'username' => $user->getUsername(),
                    'signedUrl' => $generatedSignature->getSignedUrl(),
                ]);

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Compte créé avec succès ! Veuillez vérifier votre email pour vous connecter');
            } catch (TransportExceptionInterface) {
                $this->addFlash('error', "Votre compte a été créé mais l'email de confirmation n'a pas pu être envoyé. Contactez un administrateur.");
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/index.html.twig', ['form' => $form,]);
    }

    #[Route('/inscription/verifier', name: 'app_verify_email')]
    public function verifyEmail(Request $request, VerifyEmailHelperInterface $emailHelper, EntityManagerInterface $em, UserRepository $userRepository)
    {
        $id = $request->query->get('id');
        $user = $userRepository->find($id);

        if (!$user){
            return $this->redirectToRoute('app_register');
        }

        try {
            $emailHelper->validateEmailConfirmationFromRequest($request, $user->getId(), $user->getEmail());
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);
        $em->persist($user);
        $em->flush();

        $this->addFlash('success', "Votre email a été vérifié ! Vous pouvez vous connecter");

        return $this->redirectToRoute('app_login');


    }
}
