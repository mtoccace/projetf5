<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Repository\LivreRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;



class ProfilController extends AbstractController
{
    /**
     * @Route("/profil", name="profil_index")
     */
    public function index(): Response
    {
        /* Pr avoir les infos de l'user connecté : 
        ds twig : app.user (superglobale 0 :pas co 1 : co)
        ds le controleur : $this->getUser()
        */
        $abonneConnecte = $this->getUser(); 
        return $this->render('profil/index.html.twig');
    }
    /**
     * @Route("/profil/emprunter/{id}", name="profil_emprunter")
     */
    public function emprunter(LivreRepository $lr,  EntityManagerInterface $em, Livre $livre)
    {
        $livresEmpruntes = $lr->livresEmpruntes();
        if(in_array($livre, $livresEmpruntes)){
            $this->addFlash("danger", "Le livre <strong>" . $livre->getTitre(). "</strong> n'est pas disponible" );
            return $this->redirectToRoute("accueil");
        }
        /* exo : l'user emprunte aujourd'hui le livre surlequel il a cliqué */
        $emprunt =new Emprunt; // on créé un objet, ensuite on veut mettre une valeur 
        $emprunt->setDateEmprunt(new DateTime());//newdate() créé un objet datetime avec la date du jour 
        $emprunt->setLivre($livre);//livre a été récupéré de la bdd avec l'id qui est passé ds le chemin 
        $emprunt->setAbonne($this->getUser() ); //$this->getUser() retourne un objet Abonne contenant les infos de l'abonné actuellement connecté 

        $em->persist($emprunt);//comme $emprunt est un nouvel emprunt à insérer ds la bdd il faut utiliser $em->persist
        $em->flush(); // enregistrer en bdd
        return $this->redirectToRoute("profil_index");
    }

    /* EXO : afficher le lien vers la route /profil/emprunter sur chaque vignette de livre
	⚠ le lien NE DOIT APPARAITRE que si un ROLE_LECTEUR est connecté
    reponse : 
    - ds profil controller : faire la route et la public function avec le lien  vers le profil 

    - ds vignette livre : mettre le lien path avce le name de la public function  et entouré ce lien avec une condition if isgranted role lecteur et le endif
    
    */
}
