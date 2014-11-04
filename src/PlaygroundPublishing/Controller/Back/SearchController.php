<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 04/11/2014
*
* Classe de controleur de back pour la recherche
**/

namespace PlaygroundPublishing\Controller\Back;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

class SearchController extends AbstractActionController
{
    /**
    * indexAction : Liste des articles
    *
    * @return ViewModel $viewModel 
    */
    public function searchAction()
    {
        $search = "";
        if(!empty($_GET['q'])) {
            $search = (string) $_GET['q'];
        }

        return new ViewModel(array('search' => $search));
    }
}
