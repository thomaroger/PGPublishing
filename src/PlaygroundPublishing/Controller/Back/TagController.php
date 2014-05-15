<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 15/05/2014
*
* Classe de controleur de back pour la gestion des tags
**/

namespace PlaygroundPublishing\Controller\Back;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundPublishing\Entity\Tag;

class TagController extends AbstractActionController
{
    /**
    * @var MAX_PER_PAGE  Nombre d'item par page
    */
    const MAX_PER_PAGE = 20;

    /**
    * @var Tag $tagService Service de Category
    */
    protected $tagService;

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
        $this->layout()->setVariable('subNav', "tag");
    
        $p = $this->getRequest()->getQuery('page', 1);

        $tags = $this->getTagService()->getTagMapper()->findAll();
        
        $nbTag = count($tags);

        $tagsPaginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($tags));
        $tagsPaginator->setItemCountPerPage(self::MAX_PER_PAGE);
        $tagsPaginator->setCurrentPageNumber($p);

        
        return new ViewModel(array('tags'              => $tags,
                                   'tagsPaginator'     => $tagsPaginator,
                                   'nbTag'             => $nbTag));
    }

    /**
    * createAction : Creation de layout
    *
    * @return ViewModel $viewModel 
    */
    public function createAction()
    {
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "tag");
        $return  = array();
        $data = array();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );

            $return = $this->getTagService()->checkTag($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getTagService()->create($data);

                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
            }
        }

        $tagStatuses = Tag::$statuses;
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        return new ViewModel(array('data'   => $data,
                                   'locales' => $locales,
                                   'tagStatuses' => $tagStatuses,
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
        $this->layout()->setVariable('subNav', "tag");
        $return  = array();
        $data = array();
        
        $request = $this->getRequest();

        $tagId = $this->getEvent()->getRouteMatch()->getParam('id');
        $tag = $this->getTagService()->getTagMapper()->findById($tagId);

        if(empty($tag)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
        }

        $translations = $this->getTagService()->getTagMapper()->getEntityRepositoryForEntity($tag->getTranslationRepository())->findTranslations($tag);
        $tag->setTranslations($translations);

        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );

            $return = $this->getTagService()->checkTag($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getTagService()->edit($data);

                return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
            }
        }

        $tagStatuses = Tag::$statuses;
        $locales = $this->getLocaleService()->getLocaleMapper()->findBy(array('active_front' => 1));

        return new ViewModel(array('tag' => $tag,
                                   'locales' => $locales,
                                   'tagStatuses' => $tagStatuses,
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
        $tagId = $this->getEvent()->getRouteMatch()->getParam('id');
        $tag = $this->getTagService()->getTagMapper()->findById($tagId);

        if(empty($tag)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
        }
        
        $this->getTagService()->getTagMapper()->remove($tag);

        return $this->redirect()->toRoute('admin/playgroundpublishingadmin/tags');
    }

    /**
    * getLayoutService : Recuperation du service de Layout
    *
    * @return Layout $layoutService 
    */
    private function getTagService()
    {
        if (!$this->tagService) {
            $this->tagService = $this->getServiceLocator()->get('playgroundpublishing_tag_service');
        }

        return $this->tagService;
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