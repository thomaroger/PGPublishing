<?php

/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 15/05/2014
*
* Classe qui permet de gérer le mapper de tag
**/

namespace PlaygroundPublishing\Mapper;

use Doctrine\ORM\QueryBuilder;

class Tag extends EntityMapper
{

    /**
    * findBySlug : recupere des entites en fonction de filtre
    * @param string $slug slug d'un bloc à rechercher
    *
    * @return collection $tags collection de PlaygroundPublishing\Entity\Tag
    */
    public function findBySlug($slug)
    {

       return $this->getEntityRepository()->findOneBy(array('slug' => $slug)); 
    }
  
    /**
    * getEntityRepository : recupere l'entite tags
    *
    * @return PlaygroundPublishing\Entity\Tag $tag
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundPublishing\Entity\Tag');
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
            'title' => 't.title',
            'updatedAt' => 't.updated_at',
        );
    }

    /**
    * getSupportedFilters : déclaration des filtres supportés par l'entity Block
    *
    * @return array $filters
    */
    public function getSupportedFilters()
    {
        
        return array(
            'title' => 'filterOnTitle',
        );
    }

    /**
    * filterOnName : Permet de filtrer sur 
    * @param QueryBuilder $query
    * @param string $name
    *
    * @return QueryBuilder $query
    */
    public function filterOnTitle(QueryBuilder $query, $title)
    {
        $query->where("t.title LIKE :title");
        $query->setParameter('title', (string) $title);

        return $query;
    }
}