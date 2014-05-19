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
use PlaygroundCMS\Controller\Front\AbstractActionController;

class ArticleController extends AbstractActionController
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

        $viewModel = new ViewModel(array('entity' => $entity));
        
        return $viewModel->setTemplate($this->getTemplate());
    }
}
