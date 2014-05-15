<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 15/05/2014
*
* Classe de controleur de back pour la gestion des categories
**/

namespace PlaygroundPublishing\Controller\Back;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundPublishing\Entity\Category;

class CategoryController extends AbstractActionController
{
    /**
    * @var MAX_PER_PAGE  Nombre d'item par page
    */
    const MAX_PER_PAGE = 20;

    /**
    * @var Category $categoryService Service de Category
    */
    protected $categoryService;

    /**
    * @var Locale $localeService  Service de locale
    */
    protected $localeService;

 
    /**
    * listAction : Liste des layouts
    *
    * @return ViewModel $viewModel 
    */
    public function listAction()
    {
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "category");
    
        $p = $this->getRequest()->getQuery('page', 1);

        $categories = $this->getCategoryService()->getCategoryMapper()->findAll();
        
        $nbCategory = count($categories);

        $categoriesPaginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($categories));
        $categoriesPaginator->setItemCountPerPage(self::MAX_PER_PAGE);
        $categoriesPaginator->setCurrentPageNumber($p);

        
        return new ViewModel(array('categories'              => $categories,
                                   'categoriesPaginator'     => $categoriesPaginator,
                                   'nbCategory'             => $nbCategory));
    }

    /**
    * createAction : Creation de layout
    *
    * @return ViewModel $viewModel 
    */
    public function createAction()
    {
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "category");
        $return  = array();
        $data = array();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );

            $return = $this->getCategoryService()->checkCategory($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getCategoryService()->create($data);

                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/categories');
            }
        }

        $categoryStatuses = Category::$statuses;
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        return new ViewModel(array('data'   => $data,
                                   'locales' => $locales,
                                   'categoryStatuses' => $categoryStatuses,
                                   'return' => $return));
    }

    /**
    * editAction : Edition d'un layout en fonction de son id
    * @param int $id : id du layout à editer
    *
    * @return ViewModel $viewModel 
    */
    public function editAction()
    {
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "category");
        $return  = array();
        $data = array();
        
        $request = $this->getRequest();

        $categoryId = $this->getEvent()->getRouteMatch()->getParam('id');
        $category = $this->getCategoryService()->getCategoryMapper()->findById($categoryId);

        if(empty($category)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/categories');
        }

        $translations = $this->getCategoryService()->getCategoryMapper()->getEntityRepositoryForEntity($category->getTranslationRepository())->findTranslations($category);
        $category->setTranslations($translations);

        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );

            $return = $this->getCategoryService()->checkCategory($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getCategoryService()->edit($data);

                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/categories');
            }
        }

        $categoryStatuses = Category::$statuses;
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        return new ViewModel(array('category' => $category,
                                   'locales' => $locales,
                                   'categoryStatuses' => $categoryStatuses,
                                   'return' => $return));
    }

    /**
    * removeAction : Suppression d'un layout en fonction de son id
    * @param int $id : id du layout à supprimer
    *
    * @return ViewModel $viewModel 
    */
    public function removeAction()
    {
        $categoryId = $this->getEvent()->getRouteMatch()->getParam('id');
        $category = $this->getCategoryService()->getCategoryMapper()->findById($categoryId);

        if(empty($category)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/categories');
        }
        
        $this->getCategoryService()->getCategoryMapper()->remove($category);

        return $this->redirect()->toRoute('admin/playgroundpublishingadmin/categories');
    }

    /**
    * getLayoutService : Recuperation du service de Layout
    *
    * @return Layout $layoutService 
    */
    private function getCategoryService()
    {
        if (!$this->categoryService) {
            $this->categoryService = $this->getServiceLocator()->get('playgroundpublishing_category_service');
        }

        return $this->categoryService;
    }

    /**
    * getLocaleService : Recuperation du service de locale
    *
    * @return Locale $localeService 
    */
    private function getLocaleService()
    {
        if (!$this->localeService) {
            $this->localeService = $this->getServiceLocator()->get('playgroundcore_locale_service');
        }

        return $this->localeService;
    }
}