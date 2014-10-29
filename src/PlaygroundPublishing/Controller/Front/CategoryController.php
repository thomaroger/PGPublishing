<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 22/05/2014
*
* Classe de controleur de categorie
**/
namespace PlaygroundPublishing\Controller\Front;

use Zend\View\Model\ViewModel;
use PlaygroundCMS\Controller\Front\AbstractActionController;

class CategoryController extends AbstractActionController
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

        $viewModel = new ViewModel(array('entity' => $entity,
                                        'ressource' => $ressource));
        
        return $viewModel->setTemplate($this->getTemplate());
    }
}
