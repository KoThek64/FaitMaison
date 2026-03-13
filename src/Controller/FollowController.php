<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Follow;
use App\Entity\User;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FollowController extends AbstractController
{

    #[IsGranted('ROLE_USER')]
    #[Route('/profil/{id}/suivre', name: 'app_follow_toggle', methods: ['POST'])]
    public function toggle(Request $request, User $user, FollowRepository $followRepository, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User){
            throw $this->createAccessDeniedException();
        } else if ($currentUser === $user){
            return $this->redirectToRoute('app_profile_show', ['id' => $user->getId()]);
        }

        $follow = $followRepository->findOneBy(['follower' => $currentUser, 'followed' => $user]);
        if ($follow){
            $em->remove($follow);
        } else {
            $new = new Follow();
            $new->setFollower($currentUser);
            $new->setFollowed($user);
            $em->persist($new);
        }

        $em->flush();

        if($request->isXmlHttpRequest()){
            return $this->json([
                "following" => !isset($follow),
                "count" => $followRepository->count(['followed' => $user])
            ]);
        } else {
            return $this->redirectToRoute('app_profile_show', ['id' => $user->getId()]);
        }
    }

}
