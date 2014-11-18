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
    protected $ressourceMapper;
    protected $menuMapper;
    protected $pageMapper;
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

        $em = $this->getRessourceMapper()->getEntityManager();

        // Block 
        $queryString = "SELECT b FROM PlaygroundCMS\Entity\Block b WHERE b.name LIKE :search";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $blocks = $query->getResult();

        // Layout 
        $queryString = "SELECT l FROM PlaygroundCMS\Entity\Layout l WHERE l.name LIKE :search OR l.file LIKE :search ";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $layouts = $query->getResult();

        //Menu
        $menus = array();
        $queryString = "SELECT m.foreignKey FROM PlaygroundCMS\Entity\Translation\MenuTranslation m WHERE m.content LIKE :search";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $menuIds = $query->getResult();

        foreach ($menuIds as $menuId) {
            $menu = $this->getMenuMapper()->findById($menuId['foreignKey']);
            $menus[$menu->getId()] = $menu;
        }

        //Pages
        $pages = array();
        $queryString = "SELECT p.foreignKey FROM PlaygroundCMS\Entity\Translation\PageTranslation p WHERE p.content LIKE :search";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $pageIds = $query->getResult();

        foreach ($pageIds as $pageId) {
            $page = $this->getPageMapper()->findById($pageId['foreignKey']);
            $pages[$page->getId()]['page'] = $page;
            $ressources = $this->getRessourceMapper()->findBy(array('model' => 'PlaygroundCMS\Entity\Page', 'recordId' => $page));
            $pages[$page->getId()]['ressource'] = $ressources[0];
        }

        return new ViewModel(array('search' => $search,
                                   'blocks' => $blocks,
                                   'layouts' => $layouts,
                                   'menus' => $menus,
                                   'pages' => $pages,
                                   ));
    }


    private function getRessourceMapper()
    {
        if (null === $this->ressourceMapper) {
            $this->ressourceMapper = $this->getServiceLocator()->get('playgroundcms_ressource_mapper');
        }

        return $this->ressourceMapper;
    }

    private function getMenuMapper()
    {
        if (null === $this->menuMapper) {
            $this->menuMapper = $this->getServiceLocator()->get('playgroundcms_menu_mapper');
        }

        return $this->menuMapper;
    }

    private function getPageMapper()
    {
        if (null === $this->pageMapper) {
            $this->pageMapper = $this->getServiceLocator()->get('playgroundcms_page_mapper');
        }

        return $this->pageMapper;
    }
      
}
