<?php

/**
* @package : PlaygroundPublishing\Blocks
* @author : troger
* @since : 18/03/2014
*
* Classe qui permet de gérer l'affichage d'un sondage
**/

namespace PlaygroundPublishing\Blocks;

use Zend\View\Model\ViewModel;
use PlaygroundCMS\Blocks\AbstractBlockController;

class PollController extends AbstractBlockController
{
    protected $pollMapper;
    /**
    * {@inheritdoc}
    * renderBlock : Rendu du bloc d'un bloc HTML
    */
    protected function renderBlock()
    {
        $block = $this->getBlock();
        $poll = $this->getEntity();
        $ressource = $this->getRessource();

        $params = array('block' => $block,
                        'em' => $this->getPollMapper()->getEntityManager(),
                        'poll' => $poll,
                        'ressource' => $ressource);

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
        
        return 'Block Entity Poll';
    }

    /**
    * getBlockMapper : Getter pour le blockMapper
    *
    * @return PlaygroundCMS\Mapper\Block $blockMapper : Classe de Mapper relié à l'entité Block
    */
    protected function getPollMapper()
    {
        if (empty($this->pollMapper)) {
            $this->pollMapper = $this->getServiceManager()->get('playgroundpublishing_poll_mapper');
        }

        return $this->pollMapper;
    }

   
}
