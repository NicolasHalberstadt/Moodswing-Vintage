<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UserUpdatePwdType;
use App\Form\UserUpdateType;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    /**
     * @Route("/signup", name="app_signup")
     */
    public function signup(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $google_recaptcha_site_key = $this->getParameter('google_recaptcha_site_key');

        if ($form->isSubmitted() && $form->isValid()) {
            if (isset($_POST['g-recaptcha-response'])) {
                $secret = $this->getParameter('google_recatcha_secret');
                $recaptcha = new ReCaptcha($secret);
                $resp = $recaptcha->verify($_POST['g-recaptcha-response']);

                if ($resp->isSuccess()) {
                    $plainPassword = $form->get('plain_password')->getData();
                    $encodedPassword = $encoder->encodePassword($user, $plainPassword);

                    $user->setPassword($encodedPassword);
                    $user->setRole("ROLE_USER");
                    $user->setCreatedAt(new \DateTime());
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash("success", "Your account has been successfully created");
                    return $this->redirectToRoute('app_login');
                }
            }
        }

        return $this->render('user/signup.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'google_recaptcha_site_key' => $google_recaptcha_site_key
        ]);
    }

    /**
     * @Route("user/profile", name="user_profile")
     */
    public function userProfile()
    {
        $user = $this->getUser();

        return $this->render('user/details.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/profile/update", name="user_profile_update")
     */
    public function userProfileUpdate(Request $request)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserUpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", "Your account has been successfully updated");
            return $this->redirectToRoute('user_profile');
        }

        return $this->render('user/update.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("user/password/update", name="user_password_update")
     */
    public function userPwdUpdate(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(UserUpdatePwdType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $actualPassword = $form->get('actual_password')->getData();
            $match = $encoder->isPasswordValid($user, $actualPassword);

            if ($match) {

                $newPassword =  $form->get('plain_password')->getData();
                $user->setPassword($encoder->encodePassword($user, $newPassword));
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash("success", "Your password has been successfully updated");
                return $this->redirectToRoute('user_profile');
            }
            $this->addFlash("danger", "Your current password is not correct");
            return $this->redirectToRoute('user_password_update');
        }

        return $this->render('user/update_password.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("user/delete", name="user_delete")
     */
    public function userDelete(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash("success", "Your account has been successfully deleted from our database");
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("user/favorite/{product_id}/add", name="user_favorite_add")
     */
    public function favoriteAdd($product_id)
    {

        $product = $this->getDoctrine()->getRepository(Product::class)->find($product_id);

        if ($product) {
            $user = $this->getUser();
            $user->addProduct($product);
            $product->addUser($user);

            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        $this->addFlash('danger', 'This product does not exist');
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("user/favorite/{product_id}/remove", name="user_favorite_remove")
     */
    public function favoriteRemove($product_id)
    {

        $product = $this->getDoctrine()->getRepository(Product::class)->find($product_id);

        if ($product) {
            $user = $this->getUser();
            $user->removeProduct($product);
            $product->removeUser($user);

            $manager = $this->getDoctrine()->getManager();
            $manager->flush();

            return $this->redirect($_SERVER['HTTP_REFERER']);
        }

        $this->addFlash('danger', 'This product does not exist');
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("user/favorites", name="user_favorites")
     */
    public function userFavoritesList()
    {
        $user = $this->getUser();

        return $this->render('user/favorites.html.twig', [
            'user' => $user
        ]);
    }
}
