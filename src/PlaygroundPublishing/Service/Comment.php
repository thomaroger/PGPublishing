<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 03/04/2014
*
* Classe de service pour l'entite Layout
**/
namespace PlaygroundPublishing\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundPublishing\Mapper\Comment as CommentMapper;
use PlaygroundPublishing\Entity\Comment as CommentEntity;

class Comment extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var PlaygroundCMS\Mapper\Layout layoutMapper
     */
    protected $commentMapper;

    /**
     * @var Zend\ServiceManager\ServiceManager ServiceManager
     */
    protected $serviceManager;

    /**
     * @var PlaygroundCMS\Mapper\Layout layoutMapper
     */
    protected $articleMapper;
        
    /**
    * create : Permet de créer un Layout
    * @param array $data : tableau de données 
    */
    public function create($data, $state)
    {
        $comment = new CommentEntity();
        
        $comment->setStatus($state);
        $comment->setArticle($this->getArticleMapper()->findById($data['comment']['articleId']));
        $comment->setLocale($data['comment']['locale']);
        $comment->setEmail($data['comment']['email']);
        $comment->setName($data['comment']['name']);
        $comment->setComment($data['comment']['comment']);

        $comment = $this->getCommentMapper()->insert($comment);
    }

    /**
    * checkLayout : Permet de verifier si le form est valid
    * @param array $data : tableau de données 
    *
    * @return array $result
    */
    public function checkComment($data)
    {
        if(empty($data['comment']['email'])){
            
            return array('status' => 1, 'message' => 'Email is required', 'data' => $data);
        }

        if(empty($data['comment']['name'])){
            
            return array('status' => 1, 'message' => 'Name is required', 'data' => $data);
        }

        if(empty($data['comment']['comment'])){
            
            return array('status' => 1, 'message' => 'Comment is required', 'data' => $data);
        }

        $article = $this->getArticleMapper()->findById($data['comment']['articleId']);
        if (empty($article)){

            return array('status' => 1, 'message' => 'Article is required', 'data' => $data);
        }

        return array('status' => 0, 'message' => '', 'data' => $data);
    }

    /**
     * getLayoutMapper : Getter pour categoryMapper
     *
     * @return PlaygroundCMS\Mapper\Category $categoryMapper
     */
    public function getCommentMapper()
    {
        if (null === $this->commentMapper) {
            $this->commentMapper = $this->getServiceManager()->get('playgroundpublishing_comment_mapper');
        }

        return $this->commentMapper;
    }

    /**
     * getLayoutMapper : Getter pour categoryMapper
     *
     * @return PlaygroundCMS\Mapper\Category $categoryMapper
     */
    public function getArticleMapper()
    {
        if (null === $this->articleMapper) {
            $this->articleMapper = $this->getServiceManager()->get('playgroundpublishing_article_mapper');
        }

        return $this->articleMapper;
    }

   

    /**
     * getServiceManager : Getter pour serviceManager
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

     /**
     * setServiceManager : Setter pour le serviceManager
     * @param  ServiceManager $serviceManager
     *
     * @return Block $this
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

}