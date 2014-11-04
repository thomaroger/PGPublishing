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
use PlaygroundCMS\Security\Credential;
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

    protected $layoutService;

    protected $ressourceService;

    /**
    * @var RevisionService revisionService  Service de Revision
    */
    protected $revisionService;
 
    /**
    * listAction : Liste des layouts
    *
    * @return ViewModel $viewModel 
    */
    public function listAction()
    {
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "category");
        $categoriesId = array();
        $ressourcesCollection = array();
    
        $p = $this->getRequest()->getQuery('page', 1);

        $categories = $this->getCategoryService()->getCategoryMapper()->findAll();
        
        $nbCategory = count($categories);

        $categoriesPaginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($categories));
        $categoriesPaginator->setItemCountPerPage(self::MAX_PER_PAGE);
        $categoriesPaginator->setCurrentPageNumber($p);

        foreach ($categories as $category) {
            $categoriesId[] = $category->getId();
        }

        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Category', 'recordId' => $categoriesId));
        foreach ($ressources as $ressource) {
            $ressourcesCollection[$ressource->getRecordId()][$ressource->getLocale()] = $ressource;
        }
        
        return new ViewModel(array('categories'           => $categories,
                                   'categoriesPaginator'  => $categoriesPaginator,
                                   'ressourcesCollection' => $ressourcesCollection,
                                   'nbCategory'           => $nbCategory));
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
            $data['category']['status'] = (int) $data['category']['status'];

            if ($return['status'] == 0) {
                $this->getCategoryService()->create($data);

                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/categories');
            }
        }

        $credentials = Credential::$statusesForm;
        $categoryStatuses = Category::$statuses;
        $layouts = $this->getLayoutService()->getLayoutMapper()->findAll();
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        return new ViewModel(array('data'   => $data,
                                   'locales' => $locales,
                                   'layouts' => $layouts,
                                   'credentials' => $credentials,
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
        $revisionId = $this->getEvent()->getRouteMatch()->getParam('revisionId', 0);

        $category = $this->getCategoryService()->getCategoryMapper()->findById($categoryId);

        $filters = array('type' => get_class($category), 'objectId' => $category->getId());
        $revisions = $this->getRevisionService()->getRevisionMapper()->findByAndOrderBy($filters, array('id' => 'DESC'));

        if(empty($category)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/categories');
        }

        $translations = $this->getCategoryService()->getCategoryMapper()->getEntityRepositoryForEntity($category->getTranslationRepository())->findTranslations($category);
        $category->setTranslations($translations);

        if(!empty($revisionId)){
            $revision = $this->getRevisionService()->getRevisionMapper()->findById($revisionId);
            $category = unserialize($revision->getObject());
        }

        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Category', 'recordId' => $category->getId()));

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

        $credentials = Credential::$statusesForm;
        $categoryStatuses = Category::$statuses;
        $layouts = $this->getLayoutService()->getLayoutMapper()->findAll();
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        return new ViewModel(array('category' => $category,
                                   'locales' => $locales,
                                   'layouts' => $layouts,
                                   'ressources' => $ressources,
                                   'categoryStatuses' => $categoryStatuses,
                                   'credentials' => $credentials,
                                   'revisions'   => $revisions,
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

         // Suppression des ressources associées 
        $ressources = $this->getRessourceService()->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Category', 'recordId' => $articleId));
        foreach ($ressources as $ressource) {
            $this->getRessourceService()->getRessourceMapper()->remove($ressource);
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

     /**
    * getLayoutService : Recuperation du service de Layout
    *
    * @return Layout $layoutService 
    */
    private function getLayoutService()
    {
        if (!$this->layoutService) {
            $this->layoutService = $this->getServiceLocator()->get('playgroundcms_layout_service');
        }

        return $this->layoutService;
    }

    /**
    * getRessourceService : Recuperation du service de Ressource
    *
    * @return Ressource $ressourceService 
    */
    private function getRessourceService()
    {
        if (!$this->ressourceService) {
            $this->ressourceService = $this->getServiceLocator()->get('playgroundcms_ressource_service');
        }

        return $this->ressourceService;
    }

    /**
     * getRevisionService : Recuperation du service de revision
     *
     * @return RevisionService $revisionService : revisionService
     */
    private function getRevisionService()
    {
        if (null === $this->revisionService) {
            $this->setRevisionService($this->getServiceLocator()->get('playgroundcms_revision_service'));
        }

        return $this->revisionService;
    }

    /**
     * setRevisionService : Setter du service de revision
     * @param  RevisionService $revisionService
     *
     * @return CategoryController $this
     */
    private function setRevisionService($revisionService)
    {
        $this->revisionService = $revisionService;

        return $this;
    }
}