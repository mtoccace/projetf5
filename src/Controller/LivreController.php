<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Livre;
use App\Form\LivreType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManager as ORMEntityManager;

/*

    exo  3 : Créer un contrôleur Livre

Dans ce contrôleur, créer une route "/mes-livres"

Dans la méthode (nommée mesLivres par exemple), créer une variable array qui va
contenir des arrays
titre = "Dune", auteur = "Frank Herbert"
titre = "1984", auteur = "George Orwell"
titre = "Le Seigneur des Anneaux", auteur = "J.R.R. Tolkien"

Donc dans la variable $liste, il aura 3 valeurs, 
	chaque valeur est un tableau avec un indice "titre" et un indice "auteur"
    
Afficher la liste des livres dans une table HTML

    */

    
/**
 * @Route("/admin")
 */ 
class LivreController extends AbstractController
{
    /**
     * @Route("/livre", name="livre")
     */
    public function index(livreRepository $lr): Response
    {
        return $this->render('livre/index.html.twig', [ 
        "livres"=> $lr->findAll(),
        "livres_empruntes" => $lr->livresEmpruntes()
        ]);
    }


    /**
     * @Route("/mes-livres", name="livre_mes_livres")
     */
    public function mesLivres(): Response
    {
        
        $mesLivres = [
            ["titre" => "Dune", "auteur" => "Frank Herbert" ],
            ["titre" => "1984", "auteur" => "George Orwell"], 
            ["titre" => "Le Seigneur des Anneaux", "auteur" => "J.R.R. Tolkien" ]
        ];
        return $this->render("livre/meslivres.html.twig", ["livres" =>$mesLivres ]);
    }

    /**
     * @Route("/livre/ajouter", name="livre_ajouter")
     * pr instancier un objet de la classe Request, on va utiliser l'injection de dépendance. On définit un parametre ds une methode d'un controleur de la classe Request et ds cette méthode, on pourra utiliser l'objet qui contiendra des proprietes ac ttes les valeurs des superglobales de PHP. 
     * Ex: $request->query : cette propriete est l'objet qui a les valeurs de $_GET
     * $request->request: cette propriete qui a les valeurs de $_POST 
     */

    public function ajouter(Request $request, EntityManager $em, CategorieRepository $cr)
    {
        if ($request->isMethod('POST')){ //s'il y a quechue chose ds le form
            $titre = $request->request->get("titre"); //la metode get permet de recuperer les valeurs des inputs du formulaire
            $auteur = $request->request->get("auteur"); //le parametre passé à get est le name de l'input
            $categorie_id = $request->request->get("categorie"); 
            if ($titre && $auteur){ // si titre et auteur ne st pas vides 
                $nouveauLivre = new Livre; //fait ref à l'objet livre qui est situé en haut ds use
                $nouveauLivre->setTitre($titre);
                $nouveauLivre->setAuteur($auteur);
                $nouveauLivre->setCategorie($cr ->find($categorie_id));
                /*on va utiliser l'objet $em de la classe entitymanager pr enregistrer en bdd
                - la methode persiste permet de preparer une requete INSERT INTO , le parametre doit etre un objet d'une classe Entity
                */
                $em->persist($nouveauLivre);
                $em->flush(); //la bdd est modifié
                /*la methode flush execute ttes les requetes en attente, la bdd est modifiée qd cette methode est lancée (et pas avant)
                */
                $this->addFlash("success", "Le nouveau livre a été enregistré");
                return $this->redirectToRoute('livre');// redirection vers la liste des livres
            }

        }
        /* exo : la route doit afficher un formulaire pr pouvoir ajouter un livre
        - AJouter un lien ds le menu pr accéder à cette route */
        return $this->render("livre/formulaire.html.twig", ["categories" => $cr->findAll() ]);
    }

    /**
     * @Route("/livre/modifier/{id}", name="livre_modifier")
     */
    public function modifier(EntityManager $em, Request $request, LivreRepository $lr ,$id)
    {
        $livre = $lr->find($id); //find retourne l'objet dont l'id vaut $id en BDD. Create form va creer un objet representant le formulaire créé à partir de la classe LivreType. Le 2 eme parametre est un objet qui sera lié au formulaire
        $form =$this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);
        //la methode handlerequest permet à $form de gérer les informations venant de la requete HTTP ex: est ce le formulaire a été soumis ?
        if($form->isSubmitted() && $form->isValid()){ //si le formulaire a été soumis et s'il est valide
        //ttes les modifs des objets entity qui ont été instancié à partir de la bdd vont etre enregistrées en bdd qd on va utiliser $em->flush()
            if ($fichier=$form->get("couverture")->getData() ){
            //on recuperer le nom du fichier qui a été téléversé (uploadé)
                $nomFichier = pathinfo
                ($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            //on remplace les espaces par des _
                $nomFichier =str_replace(" ", "_", $nomFichier);
                
                //on ajoute un string unique au nom du fichier pr éviter les boublons et l'extension du fichier 
                $nomFichier .=uniqid() . "." .$fichier->guessExtension();

                //on copie le fichier uploadé ds un dossier du dossier public ac le nouveau nom de fichier 
                $fichier->move($this->getParameter("dossier_images"), $nomFichier);

                //on modifie l'entité $livre 
                $livre->setCouverture($nomFichier);
            }
            



            $em->flush();
            return $this->redirectToRoute("livre");


        }

        return $this->render("livre/form.html.twig", ["formLivre" =>$form->createView() ]);
    }

    /**
     * @Route("/livre/supprimer/{id}", name="livre_supprimer")
     */

    public function supprimer(Request $request, EntityManager $em, Livre $livre){
        //si le parametre placé ds le chemin est une propriete d'une classe entity on peut récupérer directement l'objet dont la propriété vaut ce qui sera passé ds l'URL. ($livre contiendra le livre dont l'id sera passé ds l'url)

        //dd($livre); // dump & die : var dump et l'execution du code est arreté

        if($request->isMethod("POST") ){
            $em->remove($livre); //la requete DELETE est en attente
            $em->flush(); //ttes les requetes en attente sont executées 
            return $this->redirectToRoute("livre");
        }
        return $this->render("livre/supprimer.html.twig", ["livre"=>$livre]);
    }
    /**
    * @Route("/livre/fiche/{id}", name="livre_fiche")
     */ 

     public function fiche(Livre $livre)
     {
         return $this->render("livre/fiche.html.twig", compact("livre"));
         /* la fonction compact() de PHP retourne un array associatif à partir des variables qui ont le même nom que les parametres passés à compact
         Par ex : si j'ai 2 variables 
         $nom = "Ateur";
         $prenom = "Nordine";

         $personne = compact("nom", "prenom");
         est équivalent à 
         $personne = ["nom" => "Ateur", "prenom"=>"Nordine"];
         
         */
     }

     /**
    * @Route("/livre/nouveau", name="livre_nouveau")
     */ 
    public function nouveau(EntityManager $em, Request $request)
    {
        $livre = new Livre();
        $form =$this->createForm(LivreType::class, $livre);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){  

            if ($fichier=$form->get("couverture")->getData() ){
                $nomFichier = pathinfo
                ($fichier->getClientOriginalName(), PATHINFO_FILENAME);
            
                $nomFichier =str_replace(" ", "_", $nomFichier);
                
                $nomFichier .=uniqid() . "." .$fichier->guessExtension();
                
                $fichier->move($this->getParameter("dossier_images"), $nomFichier);

                $livre->setCouverture($nomFichier);
            }

            $em->persist($livre);
            $em->flush();
            return $this->redirectToRoute("livre");

        }

        return $this->render("livre/form.html.twig", ["formLivre" =>$form->createView() ]);
    }           
}


//Créer l'entité Categorie : terminal
//Mettre à jour la base de données
//Faire les routes pour ajouter, modifier, supprimer une catégorie : ds le controller 
//Afficher la liste des catégories

//Ajouter une route (livre_nouveau) pour ajouter un nouveau livre dans la bdd
//Dans le contrôleur, vous devez utiliser la classe LivreType pour générer le formulaire
	//(NB : il faut que le formulaire marche, image compris)
//Pensez à modifier les liens du menu