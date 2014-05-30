<?php
/**
* @package : PlaygroundCMS
* @author : troger
* @since : 30/05/2014
*
* Classe de controleur de back pour la gestion des commentaires
**/

namespace PlaygroundPublishing\Controller\Back;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundPublishing\Entity\Comment;

class CommentController extends AbstractActionController
{
    /**
    * @var MAX_PER_PAGE  Nombre d'item par page
    */
    const MAX_PER_PAGE = 20;

    /**
    * @var Service $pageService Service de page
    */
    protected $commentService;

    /**
    * indexAction : Liste des articles
    *
    * @return ViewModel $viewModel 
    */
    public function listAction()
    {
        $ressourcesCollection = array();
        $this->layout()->setVariable('nav', "content");
        $this->layout()->setVariable('subNav', "comment");
        $p = $this->getRequest()->getQuery('page', 1);

        $comments = $this->getCommentService()->getCommentMapper()->findByAndOrderBy(array(), array('created_at' => 'DESC'));
        
        $nbComments = count($comments);

        $commentsPaginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($comments));
        $commentsPaginator->setItemCountPerPage(self::MAX_PER_PAGE);
        $commentsPaginator->setCurrentPageNumber($p);

      
        $commentStatuses = Comment::$statuses;

        return new ViewModel(array('comments'             => $comments,
                                   'commentsPaginator'    => $commentsPaginator,
                                   'nbComments'           => $nbComments,
                                   'commentStatuses'      => $commentStatuses));
    }


    public function moderateAction()
    {
        $commentId = $this->getEvent()->getRouteMatch()->getParam('id');
        $state = $this->getEvent()->getRouteMatch()->getParam('state');
        $comment = $this->getCommentService()->getCommentMapper()->findById($commentId);

        if(empty($comment)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/comments');
        }
        $comment->setStatus($state);
        $this->getCommentService()->getCommentMapper()->update($comment);

        return $this->redirect()->toRoute('admin/playgroundpublishingadmin/comments'); 
    }

    /**
    * removeAction : Edition d'une page en fonction d'un id
    * @param int $id : Id de la page
    *
    * @return ViewModel $viewModel 
    */
    
    public function removeAction()
    {
        $commentId = $this->getEvent()->getRouteMatch()->getParam('id');
        $comment = $this->getCommentService()->getCommentMapper()->findById($commentId);

        if(empty($comment)){

            return $this->redirect()->toRoute('admin/playgroundpublishingadmin/comments');
        }

        $this->getCommentService()->getCommentMapper()->remove($comment);

        return $this->redirect()->toRoute('admin/playgroundpublishingadmin/comments');
    }



    /**
    * getPageService : Recuperation du service de page
    *
    * @return Page $pageService 
    */
    private function getCommentService()
    {
        if (!$this->commentService) {
            $this->commentService = $this->getServiceLocator()->get('playgroundpublishing_comment_service');
        }

        return $this->commentService;
    }


}
