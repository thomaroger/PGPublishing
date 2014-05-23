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
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class Comment extends EntityMapper
{
  
    /**
    * getEntityRepository : recupere l'entite category
    *
    * @return PlaygroundPublishing\Entity\Category $category
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundPublishing\Entity\Comment');
        }

        return $this->er;
    }

     /**
    * persist 
    * @param PlaygroundPublishing\Entity\Entity $entity entity
    *
    * @return PlaygroundPublishing\Entity\Entity $entity
    */
    public function persist($entity)
    {
        $this->em->persist($entity);
        $this->em->getClassMetadata('PlaygroundCMS\Entity\Zone')->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_DEFERRED_EXPLICIT;
        $this->em->flush();

        return $entity;
    }
}