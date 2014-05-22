<?php

/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 15/05/2014
*
* Classe qui permet de gérer le mapper de category
**/

namespace PlaygroundPublishing\Mapper;

use Doctrine\ORM\QueryBuilder;

class Article extends EntityMapper
{

    /**
    * findBySlug : recupere des entites en fonction de filtre
    * @param string $slug slug d'un bloc à rechercher
    *
    * @return collection $categories collection de PlaygroundPublishing\Entity\Category
    */
    public function findBySlug($slug)
    {

       return $this->getEntityRepository()->findOneBy(array('slug' => $slug)); 
    }
  
    /**
    * getEntityRepository : recupere l'entite category
    *
    * @return PlaygroundPublishing\Entity\Category $category
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundPublishing\Entity\Article');
        }

        return $this->er;
    }

    /**
    * getSupportedSorts : déclaration des tris supportés par l'entity Block
    *
    * @return array $sort
    */
    public function getSupportedSorts()
    {
        
        return array(
            'title' => 'a.title',
            'updatedAt' => 'a.updated_at',
        );
    }
}