<?php
/**
* @package : PlaygroundPublishing
* @author : troger
* @since : 14/05/2014
*
* Classe qui permet de gérer l'entity Comment 
**/
namespace PlaygroundPublishing\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="publishing_comment") 
 */
class Comment implements InputFilterAwareInterface
{   

    const ARTICLE_PENDING = 0;
    const ARTICLE_PUBLISHED = 1;
    const ARTICLE_REFUSED = 2;

    public static $statuses = array(self::ARTICLE_PENDING => "Pending Review",
                                    self::ARTICLE_PUBLISHED => "Published",
                                    self::ARTICLE_REFUSED => "Refused");
    /**
    * @var InputFilter $inputFilter
    */
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

     /**
     * @ORM\Column(type="smallint", nullable=false)
     */
    protected $status = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    protected $locale;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /**
     * @ORM\ManyToOne(targetEntity="PlaygroundPublishing\Entity\Article", inversedBy="Comment")
     */
    protected $article;

    /**
     * getId : Getter pour id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * setId : Setter pour id
     * @param integer $id 
     *
     * @return Layout $layout
     */
    public function setId($id)
    {
        $this->id = (int) $id;
        
        return $this;
    }

    /**
     * setName : Setter pour name
     * @param string $name 
     *
     * @return Layout $layout
     */
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    /**
     * getName : Getter pour name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * setFile : Setter pour file
     * @param string $file 
     *
     * @return Layout $layout
     */
    public function setEmail($email)
    {
        $this->email = (string) $email;

        return $this;
    }

    /**
     * getFile : Getter pour file
     *
     * @return string $file
     */
    public function getEmail()
    {
        return $this->email;
    }

     /**
     * setDescription : Setter pour description
     * @param string $description 
     *
     * @return Layout $layout
     */
    public function setComment($comment)
    {
        $this->comment = (string) $comment;

        return $this;
    }

    /**
     * getDescription : Getter pour description
     *
     * @return string $description
     */
    public function getComment()
    {
        return $this->comment;
    }

     /**
     * setImage : Setter pour image
     * @param string $image 
     *
     * @return Layout $layout
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

     /**
     * getImage : Getter pour image
     *
     * @return string $image
     */
    public function getLocale()
    {
        return $this->locale;
    }
    
     /**
     * getStatus : Getter pour status
     *
     * @return int $status
     */
    public function getStatus()
    {
        return $this->status;
    }

     /**
     * getStatusName : Getter pour status name
     *
     * @return string $status
     */
    public function getStatusName()
    {
        return self::$statuses[$this->status];
    }
    
    /**
     * setStatus : Setter pour status
     * @param integer $status 
     *
     * @return Page $page
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * setCreatedAt : Setter pour createdAt 
     * @param datetime $createdAt 
     *
     * @return Layout $layout
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * getCreatedAt : Getter pour created_at 
     *
     * @return datetime $created_at 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

     /**
     * setUpdatedAt : Setter pour createdAt 
     * @param datetime $updated_at 
     *
     * @return Layout $layout
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;

        return $this;
    }

   /**
     * getUpdatedAt : Getter pour updated_at 
     *
     * @return datetime $updated_at 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }


    /**
     * setCreatedAt : Setter pour createdAt 
     * @param datetime $createdAt 
     *
     * @return Layout $layout
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * getCreatedAt : Getter pour created_at 
     *
     * @return datetime $created_at 
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * getArrayCopy : Convertit l'objet en tableau.
     *
     * @return array $array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * populate : Populate l'object à partir d'un array
     * @param array $data
     *
     */
    public function populate($data = array())
    {
        
    }

     /**
     * setInputFilter : Rajoute des Filtres
     * @param InputFilterInterface $inputFilter
     *
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    /**
     * getInputFilter : Rajoute des Filtres
     *
     * @return InputFilter $inputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /** @PrePersist */
    public function createChrono()
    {
        $this->created_at = new \DateTime("now");
        $this->updated_at = new \DateTime("now");
    }

    /** @PreUpdate */
    public function updateChrono()
    {
        $this->updated_at = new \DateTime("now");
    }
}