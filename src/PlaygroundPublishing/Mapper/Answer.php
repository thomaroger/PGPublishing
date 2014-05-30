<?php

/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 15/05/2014
*
* Classe qui permet de gérer le mapper de answer
**/

namespace PlaygroundPublishing\Mapper;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class Answer extends EntityMapper
{
  
    /**
    * getEntityRepository : recupere l'entite category
    *
    * @return PlaygroundPublishing\Entity\Category $category
    */
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundPublishing\Entity\Answer');
        }

        return $this->er;
    }
}