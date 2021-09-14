<?php

namespace App\Controller;

use App\Entity\Abonne;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/inscription", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new Abonne();
        $form = $this->createForm (RegistrationFormType::class, $user);
        $form->handleRequest($request); //formulaire soumis

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setRoles(["ROLE_LECTEUR"]);//on veut que chaque nvx inscrit est un role LECTEUR automatiquement 


//enregistrement en bdd
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('accueil');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
/* Chaque fois qu'un utilisateur s'inscrit, il doit avoir le rôle ROLE_LECTEUR 
enregistré en bdd

Rappel : la propriété roles est un ARRAY qui contient des chaînes de caractères */