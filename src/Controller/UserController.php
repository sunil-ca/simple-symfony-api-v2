<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user/login/{email}", name="login")
     */
    public function login($email, SessionInterface $session, LoggerInterface $logger)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->findOneBy(['email' => $email]);

        if (!$user)
        {
            $session->set('valid_user', false);

            //log user to log-file
            $logger->error("Login Failed for user - " . $email);

            return $this->json(array('status' => 'fail', 'message' => 'invalid user.'));
        }

        $session->set('valid_user', true);
        
        //log user to log-file
        $logger->info("Login Success - " . $email);

        return $this->json(array('status' => 'success', 'message' => 'user logged-in successful.'));
    }
}
