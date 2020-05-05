<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact",
     * options={"sitemap" = {"priority" = 0.7, "changefreq" = "weekly" }})
     */
    public function contact(Request $request, MailerInterface $mailer)
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $contactFormData = $form->getData();

            $email = (new Email())
                ->from('contactFormMail@gmail.com')
                ->to('moodswingvintage@gmail.com')
                ->subject('New Contact form request')
                ->text('You have a new contact request from ' . $contactFormData['name'] . ', ' . $contactFormData['email'] . ' saying : ' . $contactFormData['text'], 'text/plain');
            $mailer->send($email);

            $this->addFlash('success', 'Your contact request has been sent');

            $email = (new TemplatedEmail())
                ->from('moodswingvintage@gmail.com')
                ->to($contactFormData['email'])
                ->subject('Your contact request')
                ->htmlTemplate('emails/contact_confirmation.html.twig')
                ->context([
                    'user' => $contactFormData['name']
                ]);
            $mailer->send($email);

            return $this->redirectToRoute('homepage');
        }

        return $this->render('contact/form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
