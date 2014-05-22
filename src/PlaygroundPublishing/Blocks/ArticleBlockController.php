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
    protected $articleMapper;
    /**
    * {@inheritdoc}
    * renderBlock : Rendu du bloc d'un bloc HTML
    */
    protected function renderBlock()
    {
        $block = $this->getBlock();
        $article = $this->getEntity();
        $ressource = $this->getRessource();

        $params = array('block'     => $block,
                        'ressource' => $ressource,
                        'em'       => $this->getArticleMapper()->getEntityManager(),
                        'article'  => $this->getEntity());

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
    protected function getArticleMapper()
    {
        if (empty($this->articleMapper)) {
            $this->articleMapper = $this->getServiceManager()->get('playgroundpublishing_article_mapper');
        }

        return $this->articleMapper;
    }

   
}
