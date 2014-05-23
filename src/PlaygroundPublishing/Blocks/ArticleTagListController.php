<?php

/**
* @package : PlaygroundPublishing\Blocks
* @author : troger
* @since : 20/05/2014
*
* Classe qui permet de gérer l'affichage d'une liste article en fonction d'une category
**/

namespace PlaygroundPublishing\Blocks;

use Zend\View\Model\ViewModel;
use PlaygroundCMS\Blocks\AbstractListController;

class ArticleTagListController extends AbstractListController
{
    protected $tagMapper;
    protected $articleMapper;
    /**
    * {@inheritdoc}
    * renderBlock : Rendu du bloc d'un bloc HTML
    */
    protected function renderBlock()
    {
        $block = $this->getBlock();
        $ressource = $this->getRessource();
        $articles =  array();
        $tagsId = array();


        if($block->getParam('current_entity', 0) == false) {
            // Pas d'utilisation d'entité courante
            $tagsId = array($block->getParam('tag'));
            $tags = array($this->getTagMapper()->findById($tagsId[0]));
        } else {
            // Utiliastion de l'entité courante
            $entity = $this->getEntity();
            if (get_class($entity) == 'PlaygroundPublishing\Entity\Tag') {
                $tags = array($entity);
            }else {
                $tags = $entity->getTags();
            }
            foreach ($tags as $tag) {
                $tagsId[] = $tag->getId();
            }
        }

        // Le block a besoin d'une categorie pour fonctionner
        if (empty($tagsId)) {
             throw new \RuntimeException(sprintf(
                'ArticleTagListController::renderBlock have to a tag for filter articles'
            ));
        }
        
        
        $query = $this->getBlockMapper()->getQueryBuilder();
        $query = $query->select('a')->from('PlaygroundPublishing\Entity\Article', 'a');

        // Filtre sur la categorie
        $query->leftJoin('a.tags', 't');
        $query->andWhere("t.id IN (".implode(',',$tagsId).")");

        // Filter par defaut
        if (method_exists($this->getBlockMapper(), "defaultFilters")) {
           $query = $this->getBlockMapper()->defaultFilters($query);
        }

        // Filtre sur l'article si entite courante
        if (get_class($entity) == 'PlaygroundPublishing\Entity\Article') {
            if($block->getParam('current_entity', 0) == true) {
                $query->andWhere("a.id != :id");
                $query->setParameter('id', (int) $entity->getId());
            }
        }
        
        $query = $this->addSort($query);   
        list($articles, $countArticles) = $this->addPager($query); 
 
        $params = array('block'         => $block,
                        'ressource'     => $ressource,
                        'em'            => $this->getBlockMapper()->getEntityManager(),
                        'tags'    => $tags,
                        'countArticles' => $countArticles,
                        'articles'      => $articles);

        $model = new ViewModel($params);
        
        return $this->render($model);
    }
    
    /**
    * __toString : Permet de decrire le bloc
    *
    * @return string $return : Block HTML
    */
    public function __toString()
    {
        
        return 'Block list Article by tags';
    }

    
    /**
    * getBlockMapper : Getter pour le blockMapper
    *
    * @return PlaygroundCMS\Mapper\Block $blockMapper : Classe de Mapper relié à l'entité Block
    */
    protected function getBlockMapper()
    {
        if (empty($this->articleMapper)) {
            $this->articleMapper = $this->getServiceManager()->get('playgroundpublishing_article_mapper');
            $this->setBlockMapper($this->articleMapper);
        }

        return $this->articleMapper;
    }

     /**
    * getBlockMapper : Getter pour le blockMapper
    *
    * @return PlaygroundCMS\Mapper\Block $blockMapper : Classe de Mapper relié à l'entité Block
    */
    protected function getTagMapper()
    {
        if (empty($this->tagMapper)) {
            $this->tagMapper = $this->getServiceManager()->get('playgroundpublishing_tag_mapper');
        }

        return $this->tagMapper;
    }
}