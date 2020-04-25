<?php

namespace App\Controller;

use App\Form\ForgotPasswordType;
use App\Form\NewPasswordType;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->addFlash("success", "Welcome" . $this->getUser());
            return $this->redirectToRoute('homepage');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("user/password/forgot", name="user_password_forgot")
     */
    public function ForgottenPassword(Request $request, UserRepository $userRepo, MailerInterface $mailer, UserPasswordEncoderInterface $encoder, TokenGeneratorInterface $tokenGenerator)
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $userEmail = $form->get('email')->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $user = $userRepo->findOneBy(['email' => $userEmail]);

            if ($user === null) {
                $this->addFlash('danger', 'Email unknown');
                return $this->redirectToRoute('app_login');
            }
            $token = $tokenGenerator->generateToken();

            try {
                $user->setResetToken($token);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('homepage');
            }

            $url = $this->generateUrl('user_password_reset', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new TemplatedEmail())
                ->from('moodswingvintage@gmail.com')
                ->to($userEmail)
                ->subject('Password renewal')
                ->htmlTemplate('emails/forgot_password.html.twig')
                ->context([
                    'url' => $url,
                    'user' => $user
                ]);

            $mailer->send($email);

            $this->addFlash("success", "An email containing a link to reset your password has just been sent to you !");
            return $this->redirectToRoute('homepage');
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/reset_password/{token}", name="user_password_reset")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder, UserRepository $userRepo)
    {
        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $user = $userRepo->findOneBy(['resetToken' => $token]);

            if ($user === null) {
                $this->addFlash('danger', 'Token unknown');
                return $this->redirectToRoute('homepage');
            }

            dump($form->get('new_password')->getData());
            $user->setResetToken(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $form->get('new_password')->getData()));
            $entityManager->flush();

            $this->addFlash('success', 'Mot de passe mis à jour');

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'security/reset_password.html.twig',
            [
                'token' => $token,
                'form' => $form->createView()
            ]
        );
    }
}
