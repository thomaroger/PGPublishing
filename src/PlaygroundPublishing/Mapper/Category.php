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
use PlaygroundPublishing\Entity\Category as CategoryEntity;

class Category extends EntityMapper
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
            $this->er = $this->em->getRepository('PlaygroundPublishing\Entity\Category');
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
            'title' => 'c.title',
            'updatedAt' => 'c.updated_at',
        );
    }

    public function defaultFilters($query)
    {

        $query->andWhere("c.status = :status");
        $query->setParameter('status',  CategoryEntity::CATEGORY_PUBLISHED);

        return $query;
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
        $query->andWhere("c.title LIKE :title");
        $query->setParameter('title', (string) $title);

        return $query;
    }
}