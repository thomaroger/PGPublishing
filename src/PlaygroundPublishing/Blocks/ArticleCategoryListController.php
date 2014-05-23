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

class ArticleCategoryListController extends AbstractListController
{
    protected $tagMapper;
    protected $categoryMapper;
    /**
    * {@inheritdoc}
    * renderBlock : Rendu du bloc d'un bloc HTML
    */
    protected function renderBlock()
    {
        $block = $this->getBlock();
        $ressource = $this->getRessource();
        $articles =  array();
        $categoriesId = array();

        if($block->getParam('current_entity', 0) == false) {
            // Pas d'utilisation d'entité courante
            $categoriesId = array($block->getParam('category'));
            $categories = array($this->getCategoryMapper()->findById($categoriesId[0]));
        } else {
            // Utiliastion de l'entité courante
            $entity = $this->getEntity();
            if (get_class($entity) == 'PlaygroundPublishing\Entity\Category') {
                $categories = array($entity);
            }else {
                $categories = $entity->getCategories();
            }
            foreach ($categories as $category) {
                $categoriesId[] = $category->getId();
            }
        }

        // Le block a besoin d'une categorie pour fonctionner
        if (empty($categoriesId)) {
             throw new \RuntimeException(sprintf(
                'ArticleCategoryListController::renderBlock have to a category for filter articles'
            ));
        }

        $query = $this->getBlockMapper()->getQueryBuilder();
        $query = $query->select('a')->from('PlaygroundPublishing\Entity\Article', 'a');

        // Filtre sur la categorie
        $query->leftJoin('a.categories', 'c');
        $query->andwhere("c.id IN (".implode(',',$categoriesId).")");

        // Filter par defaut
        if (method_exists($this->getBlockMapper(), "defaultFilters")) {
           $query = $this->getBlockMapper()->defaultFilters($query);
        }
        

        // Filtre sur l'article courant si entite courante
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
                        'categories'    => $categories,
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
        
        return 'Block list Article by Category';
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
    protected function getCategoryMapper()
    {
        if (empty($this->categoryMapper)) {
            $this->categoryMapper = $this->getServiceManager()->get('playgroundpublishing_category_mapper');
        }

        return $this->categoryMapper;
    }
}