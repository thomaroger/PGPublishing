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
use PlaygroundPublishing\Entity\Article as ArticleEntity;

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

    public function defaultFilters($query)
    {
        // Status publié
        $query->andWhere("a.status = :status");
        $query->setParameter('status',  ArticleEntity::ARTICLE_PUBLISHED);

        // Période entre la date de debut et la date de fin
        $currentTime = date('Y-m-d h:i:s');
        $query->andWhere("a.startDate < :currentDate");
        $query->andWhere("a.endDate > :currentDate");
        $query->setParameter('currentDate',  $currentTime);

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
        
        $query->andWhere("a.title LIKE :title");
        $query->setParameter('title', (string) $title);

        return $query;
    }
}