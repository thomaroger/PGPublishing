<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 18/03/2014
*
* Classe de controleur d'article
**/
namespace PlaygroundPublishing\Controller\Front;

use Zend\View\Model\ViewModel;
use PlaygroundCMS\Controller\Front\AbstractEntityActionController;

class ArticleController extends AbstractEntityActionController
{
    /**
    * indexAction : Index du Controller de page
    *
    * @return ViewModel $viewModel 
    */
    public function indexAction()
    {
        $ressource = $this->getRessource();
        $entity = $this->getEntity();

        $result = $entity->checkVisibility();

        if($result === false){
            $this->getResponse()->setStatusCode(404);

            return;
        }

        if(!$entity->getIsWeb()) {
            $this->getResponse()->setStatusCode(404);

            return;
        }


        $format = $this->getEvent()->getRouteMatch()->getParam('format');
        if($format != 'html'){
            
            return $this->renderEntityForExport();

        }
        
        $viewModel = new ViewModel(array('entity' => $entity,
                                        'ressource' => $ressource));
            
        return $viewModel->setTemplate($this->getTemplate());
    }
}
