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

class ArticleHTMLController extends AbstractBlockController
{
    /**
    * {@inheritdoc}
    * renderBlock : Rendu du bloc d'un bloc HTML
    */
    protected function renderBlock()
    {
        $block = $this->getBlock();
        $params = array('block' => $block);
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
}
