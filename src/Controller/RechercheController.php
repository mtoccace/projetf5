<?php

namespace App\Controller;

use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RechercheController extends AbstractController
{
    /**
     * @Route("/recherche", name="recherche_index")
     */
    public function index(LivreRepository $lr, Request $rq): Response
    {
        $mot = $rq->query->get("search"); //je recupere le mot ds la barre de recherche 
        $livres=$lr->recherche($mot);//je recupere ts les livres dont celui qui est = à la valuer de $mot 
        $livres_empruntes = $lr->livresEmpruntes();
        return $this->render('recherche/index.html.twig', compact("livres", "mot", "livres_empruntes")); //elle renvoie le livre recherché grace à compact
    }
}
