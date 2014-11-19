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
    protected $layoutZoneService;
    protected $articleMapper;
    protected $categoryMapper;
    protected $pollMapper;
    protected $tagMapper;
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

        $queryString = "SELECT t FROM PlaygroundCMS\Entity\Template t WHERE t.name LIKE :search OR t.file LIKE :search ";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $templates = $query->getResult();

        $queryString = "SELECT z FROM PlaygroundCMS\Entity\Zone z WHERE z.name LIKE :search";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $zones = $query->getResult();

        $layoutsPerZone = array();
        foreach ($zones as $zone) {
            $layoutsZone = $this->getLayoutZoneService()->getLayoutZoneMapper()->findBy(array('zone' => $zone));
            foreach ($layoutsZone as $layoutZone) {
                $layoutsPerZone[$zone->getId()][] = $layoutZone->getId(); 
            }
        }

        $articles = array();
        $queryString = "SELECT p.foreignKey FROM PlaygroundPublishing\Entity\Translation\ArticleTranslation p WHERE p.content LIKE :search";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $articleIds = $query->getResult();

        foreach ($articleIds as $articleId) {
            $article = $this->getArticleMapper()->findById($articleId['foreignKey']);
            $articles[$article->getId()]['article'] = $article;
            $ressources = $this->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Article', 'recordId' => $articleId));
            $articles[$article->getId()]['ressource'] = $ressources[0];
        }

        $categories = array();
        $queryString = "SELECT p.foreignKey FROM PlaygroundPublishing\Entity\Translation\CategoryTranslation p WHERE p.content LIKE :search";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $categoriesIds = $query->getResult();

        foreach ($categoriesIds as $categoryId) {
            $category = $this->getCategoryMapper()->findById($categoryId['foreignKey']);
            $categories[$category->getId()]['category'] = $category;
            $ressources = $this->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Category', 'recordId' => $categoryId));
            $categories[$category->getId()]['ressource'] = $ressources[0];
        }

        $polls = array();
        $queryString = "SELECT p.foreignKey FROM PlaygroundPublishing\Entity\Translation\PollTranslation p WHERE p.content LIKE :search";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $pollsIds = $query->getResult();

        foreach ($pollsIds as $pollId) {
            $poll = $this->getPollMapper()->findById($pollId['foreignKey']);
            $polls[$poll->getId()]['poll'] = $poll;
            $ressources = $this->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Poll', 'recordId' => $pollId));
            $polls[$poll->getId()]['ressource'] = $ressources[0];
        }

        $tags = array();
        $queryString = "SELECT p.foreignKey FROM PlaygroundPublishing\Entity\Translation\TagTranslation p WHERE p.content LIKE :search";
        $query = $em->createQuery($queryString)->setParameter('search', '%'.$search.'%');
        $tagsIds = $query->getResult();

        foreach ($tagsIds as $tagId) {
            $tag = $this->getTagMapper()->findById($tagId['foreignKey']);
            $tags[$tag->getId()]['tag'] = $tag;
            $ressources = $this->getRessourceMapper()->findBy(array('model' => 'PlaygroundPublishing\Entity\Tag', 'recordId' => $tagId));
            $tags[$tag->getId()]['ressource'] = $ressources[0];
        }

        return new ViewModel(array('search' => $search,
                                   'blocks' => $blocks,
                                   'layouts' => $layouts,
                                   'menus' => $menus,
                                   'pages' => $pages,
                                   'templates' => $templates,
                                   'zones' => $zones,
                                   'layoutsPerZone' => $layoutsPerZone,
                                   'articles' => $articles,
                                   'categories' => $categories,
                                   'polls' => $polls,
                                   'tags' => $tags,
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

    private function getArticleMapper()
    {
        if (null === $this->articleMapper) {
            $this->articleMapper = $this->getServiceLocator()->get('playgroundpublishing_article_mapper');
        }

        return $this->articleMapper;
    }

    private function getCategoryMapper()
    {
        if (null === $this->categoryMapper) {
            $this->categoryMapper = $this->getServiceLocator()->get('playgroundpublishing_category_mapper');
        }

        return $this->categoryMapper;
    }

    private function getPollMapper()
    {
        if (null === $this->pollMapper) {
            $this->pollMapper = $this->getServiceLocator()->get('playgroundpublishing_poll_mapper');
        }

        return $this->pollMapper;
    }

    private function getTagMapper()
    {
        if (null === $this->tagMapper) {
            $this->tagMapper = $this->getServiceLocator()->get('playgroundpublishing_tag_mapper');
        }

        return $this->tagMapper;
    }

    private function getLayoutZoneService()
    {
        if (!$this->layoutZoneService) {
            $this->layoutZoneService = $this->getServiceLocator()->get('playgroundcms_layoutzone_service');
        }

        return $this->layoutZoneService;
    }
      
}
