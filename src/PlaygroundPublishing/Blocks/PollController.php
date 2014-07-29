<?php

/**
* @package : PlaygroundPublishing\Blocks
* @author : troger
* @since : 18/03/2014
*
* Classe qui permet de gérer l'affichage d'un sondage
**/

namespace PlaygroundPublishing\Blocks;

use Zend\View\Model\ViewModel;
use PlaygroundCMS\Blocks\AbstractBlockController;

class PollController extends AbstractBlockController
{
    protected $pollMapper;
    /**
    * {@inheritdoc}
    * renderBlock : Rendu du bloc d'un bloc HTML
    */
    protected function renderBlock()
    {
        $result = false;
        $block = $this->getBlock();
        $poll = $this->getEntity();
        $answers = $poll->getAnswers();
        $ressource = $this->getRessource();
        $nbVote = 0;
        $maxVote = 0;
        $equals = true;


        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );

            $this->getAnswerService()->addVoteToAnAnswer($data);
            $result = true;
        }

        foreach ($answers as $answer) {
            $nbVote += $answer->getCount();
            if ($answer->getCount() > $maxVote) {
                $maxVote = $answer->getCount();
            }
        }

        if ($poll->isFinished()) {
            $result = true;
        }

        $params = array('block'     => $block,
                        'em'        => $this->getPollMapper()->getEntityManager(),
                        'poll'      => $poll,
                        'answers'   => $answers,
                        'ressource' => $ressource,
                        'maxVote'   => $maxVote,
                        'nbVote'    => $nbVote,
                        'result'    => $result);

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
        
        return 'Block Entity Poll';
    }

    /**
    * getBlockMapper : Getter pour le blockMapper
    *
    * @return PlaygroundCMS\Mapper\Block $blockMapper : Classe de Mapper relié à l'entité Block
    */
    protected function getPollMapper()
    {
        if (empty($this->pollMapper)) {
            $this->pollMapper = $this->getServiceManager()->get('playgroundpublishing_poll_mapper');
        }

        return $this->pollMapper;
    }

    public function getAnswerService()
    {
        if (empty($this->answserService)) {
            $this->answserService = $this->getServiceManager()->get('playgroundpublishing_answer_service');
        }

        return $this->answserService;
    }

   
}
