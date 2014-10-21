<?php

/**
* @package : PlaygroundPublishing\Blocks
* @author : troger
* @since : 18/03/2014
*
* Classe qui permet de gérer l'affichage d'un article
**/

namespace PlaygroundPublishing\Blocks;

use Zend\View\Model\ViewModel;
use PlaygroundCMS\Blocks\AbstractBlockController;
use PlaygroundPublishing\Entity\Comment;
use Doctrine\Common\Collections\Criteria;

class CommentListController extends AbstractBlockController
{
    const MAX_PER_PAGE = 5;

    protected $commentService;
    /**
    * {@inheritdoc}
    * renderBlock : Rendu du bloc d'un bloc HTML
    */
    protected function renderBlock()
    {
        $block = $this->getBlock();
        $article = $this->getEntity();
        $ressource = $this->getRessource();
        $data = array();
        $return = array();
        $p = $this->getRequest()->getQuery('page', 1);


        $filters = array('article' => $article, 
                         'locale' => $ressource->getLocale(),
                         'status' => Comment::COMMENT_PUBLISHED);

        $comments = $this->getCommentService()->getCommentMapper()->findByAndOrderBy($filters, array('updated_at' => Criteria::DESC));
        $nbComments = count($comments);
        
        //Mettre en param le nombre de commentaire
        $commentsPaginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($comments));
        $commentsPaginator->setItemCountPerPage(self::MAX_PER_PAGE);
        $commentsPaginator->setCurrentPageNumber($p);
        

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );

            $return = $this->getCommentService()->checkComment($data);
            $data = $return["data"];
            unset($return["data"]);

            if ($return['status'] == 0) {
                $this->getCommentService()->create($data, $block->getParam('status', 0));

                    $response = $this->getResponse();
                    $response->getHeaders()->addHeaderLine('Location', $ressource->getUrl());
                    $response->setStatusCode(302);
                    return $response;
            }
        }

        $params = array('block'     => $block,
                        'article'   => $article,
                        'ressource' => $ressource,
                        'data'      => $data,
                        'return'    => $return,
                        'comments'  => $comments,
                        'uri'       => $request->getUri()->getPath(),
                        'nbComments' => $nbComments,
                        'commentsPaginator'  => $commentsPaginator);

        $model = new ViewModel($params);
        
        return $this->render($model);
    }
    
    /**
    * __toString : Permet de decrire le bloc
    *
    * @return string $return : Block HTML
    */
    public function __toString()
    {
        
        return 'Block List Comment';
    }

    /**
    * getBlockMapper : Getter pour le blockMapper
    *
    * @return PlaygroundCMS\Mapper\Block $blockMapper : Classe de Mapper relié à l'entité Block
    */
    protected function getCommentService()
    {
        if (empty($this->commentService)) {
            $this->commentService = $this->getServiceManager()->get('playgroundpublishing_comment_service');
        }

        return $this->commentService;
    }

   
}
