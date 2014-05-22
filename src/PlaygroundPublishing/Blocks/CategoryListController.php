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

class CategoryListController extends AbstractListController
{
   /**
    * @var PlaygroundCMS\Mapper\* $blockMapper: Classe de Mapper relié à l'entité Block
    */
    protected $blockMapper;

    /**
    * {@inheritdoc}
    * renderBlock : Rendu du bloc de liste de l'entité block avec filtres, tris et pagination
    */
    protected function renderBlock()
    {
        $request = $this->getRequest();

        $block = $this->getBlock();
        $query = $this->getBlockMapper()->getQueryBuilder();
        $query = $query->select('c')->from('PlaygroundPublishing\Entity\category', 'c');

        $query = $this->addFilters($query);
        $query = $this->addSort($query);   
             
        list($results, $countResults) = $this->addPager($query);

        $params = array('block' => $block,
                        'results' => $results,
                        'countResults' => $countResults,
                        'ressource' => $this->getRessource(),
                        'em' => $this->getBlockMapper()->getEntityManager(),
                        'uri' => $request->getUri()->getPath());

        $model = new ViewModel($params);
        
        return $this->render($model);
    }

    /**
    * __toString : Permet de decrire le bloc
    *
    * @return string $return : Block list block
    */
    public function __toString()
    {
        
        return 'category list Block';
    }

    /**
    * getBlockMapper : Getter pour le blockMapper
    *
    * @return PlaygroundCMS\Mapper\Block $blockMapper : Classe de Mapper relié à l'entité Block
    */
    protected function getBlockMapper()
    {
        if (empty($this->blockMapper)) {
            $this->setBlockMapper($this->getServiceManager()->get('playgroundpublishing_category_mapper'));
        }

        return $this->blockMapper;
    }
}