<?php

/**
* @package : PlaygroundPublishing\Blocks
* @author : troger
* @since : 18/03/2014
*
* Classe qui permet de gérer l'affichage d'un article
**/

namespace PlaygroundPublishing\Blocks;

use Zend\View\Model\ViewModel;
use PlaygroundCMS\Blocks\AbstractBlockController;

class ArticleBlockController extends AbstractBlockController
{
    protected $tagMapper;
    /**
    * {@inheritdoc}
    * renderBlock : Rendu du bloc d'un bloc HTML
    */
    protected function renderBlock()
    {
        $block = $this->getBlock();
        $article = $this->getEntity();
        $ressource = $this->getRessource();

        foreach ($article->getTags() as $tag) {
            $translations = $this->getTagMapper()->getEntityRepositoryForEntity($tag->getTranslationRepository())->findTranslations($tag);
            $tag->setTranslations($translations[$ressource->getLocale()]);
        }

         foreach ($article->getCategories() as $category) {
            $translations = $this->getCategoryMapper()->getEntityRepositoryForEntity($category->getTranslationRepository())->findTranslations($category);
            $category->setTranslations($translations[$ressource->getLocale()]);
        }


        $params = array('block' => $block,
                        'article' => $this->getEntity());

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
        
        return 'Block Entity Article';
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
