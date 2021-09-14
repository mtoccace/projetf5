<?php

namespace App\Repository;

use App\Entity\Emprunt;
use App\Entity\Livre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Livre|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livre|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livre[]    findAll()
 * @method Livre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livre::class);
    }

    /**
    * @return Livre[] Returns an array of Livre objects
    */
    
    public function recherche($value)
    {
        /* SELECT * 
        FROM livre l  
        WHERE l.titre 
        LIKE "%xxx%" OR l.auteur LIKE "%xxx%"
        ORDER BY l.titre, l.auteur */
        return $this->createQueryBuilder('l') //le parametre l represente la table livre (comme un alias ds une requelte sql) l : table livre / createquerybuilder: elle créée une requete sql 
            ->where('l.titre LIKE :val') //where ou andWhere c'est pareil
            ->orWhere("l.auteur LIKE :val") 
            ->setParameter('val', "%$value%")//val est le parametre qui va avoir comme valeur ce qu'il y a ds $value , attention double apostrophe obligatoire, %:peu importe ce qu'il y a a gauche et à droite
            ->orderBy('l.titre', 'ASC')
            ->addOrderBy("l.auteur")
            ->getQuery() //requete va etre executee
            ->getResult()//je recupere le resultat
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Livre
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */




//requete avec JOINTURE : 

    /*  SELECT l.*
    FROM livre l JOIN emprunt e ON e.livre_id = l.id
    WHERE date_retour IS NULL */
    public function livresEmpruntes()
    {
    
        return $this->createQueryBuilder('l') 
            ->join(Emprunt::class, "e", "WITH", "e.livre = l.id")
            ->andWhere('e.date_retour IS NULL') 
            ->orderBy('l.auteur')
            ->addOrderBy('l.titre')
            ->getQuery() 
            ->getResult()
        ;
    }
}
