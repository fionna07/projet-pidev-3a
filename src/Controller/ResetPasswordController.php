<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

//envoi d'email'
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class ResetPasswordController extends AbstractController
{

    #[Route('/email', name: 'app_email')]
    public function emailVerification(SessionInterface $session, Request $request): Response
    {
        $session->invalidate();
        $error = $request->query->get('error');
        return $this->render('reset_password/verifyEmail.html.twig', [
            'error' => $error,
        ]);
    }



    #[Route('/verifieremail', name: 'app_verifyemail')]
    public function verifyEmail(Request $request, ManagerRegistry $doctrine, SessionInterface $session): Response

    {
        $email = $request->request->get('email');
        $entityManager = $doctrine->getManager();
        $user= $entityManager->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->redirectToRoute('app_email', ['error' => 'invalid_email'] );
        }

        $userId = $user->getId();
        $OTP=rand(1000, 9999);

//        envoi d'email'

        $transport = Transport::fromDsn('smtp://benharbfarah85@gmail.com:usvjuzoqaluwufif@smtp.gmail.com:587?encryption=tls&auth_mode=login');
        $mailer = new Mailer($transport);

        $emailOTP = (new Email())
            ->from(new Address('wefarm.support@gmail.com', 'Wefarm Support'))
            ->to($email)
            ->subject('Demande de réinitialisation de mot de passe')
            ->html('<p>Bonjour,</p><p>Vous avez demandé la réinitialisation de votre mot de passe. 
            Voici votre code de validation : <strong>' . $OTP . '</strong>.</p><p>Cordialement,<br>Wefarm Support</p>');
        $mailer->send($emailOTP);

//        fin d'email'



        $session->set('userId', $userId);
        $session->set('OTP', $OTP);
        $session->set('email',$email);

        return $this->render('reset_password/OTP.html.twig');
    }

    #[Route('/otp', name: 'app_otp')]
    public function otpValidation(SessionInterface $session): Response
    {
        $email=$session->get('email');
        $OTP=$session->get('OTP');
        $userId=$session->get('userId');
        if(!$userId){
            return $this->redirectToRoute('app_email');
        }
        return $this->render('reset_password/OTP.html.twig');
    }

    #[Route('/verifierotp', name: 'app_verifyotp')]
    public function verifyOtp(Request $request, SessionInterface $session): Response
    {
        $otp1 = $request->request->get('otp1');
        $otp2 = $request->request->get('otp2');
        $otp3 = $request->request->get('otp3');
        $otp4 = $request->request->get('otp4');
        $userOtp = intval($otp1 . $otp2 . $otp3 . $otp4);
        $session->set('userOTP', $userOtp);

        $OTPFromSession = $session->get('OTP');
        $email = $session->get('email');

        if($userOtp !== $OTPFromSession){
//            $flashy->error('OTP est incorrecte', 'http://your-awesome-link.com');
            return $this -> render('reset_password/OTP.html.twig', ['email' =>$email,  'OTP'=>$OTPFromSession] );
        }

        return $this->render('reset_password/resetPassword.html.twig');
    }

    #[Route('/resetpassword', name: 'app_reset_password')]
    public function resetPassword(SessionInterface $session): Response
    {
        $userOTP=$session->get('userOTP');
        if(!$userOTP){
            return $this->redirectToRoute('app_email');
        }
        return $this->render('reset_password/resetPassword.html.twig');
    }

    #[Route('/modifiermotdepasse', name: 'app_modifypassword')]
    public function modifierMotdepasse(Request $request, SessionInterface $session, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): Response
    {
        $userId = $session->get('userId');
        $entityManager = $doctrine->getManager();
        $user= $entityManager->getRepository(Utilisateur::class)->find($userId);
        $password = $request->request->get('password');
        if ($password === null) {
//            $flashy->success('Invalid Mot de passe ', 'http://your-awesome-link.com');
            return $this->redirectToRoute('app_email');
        }
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $password
        );
        $user->setPassword($hashedPassword);
        $session->invalidate();
        $entityManager->flush();

//        $flashy->success('Votre mot de passe a été changé avec succès.');
//        $session->set('flashy_message', 'Votre mot de passe a été changé avec succès.');
        return $this->redirectToRoute('app_login');
    }





}
