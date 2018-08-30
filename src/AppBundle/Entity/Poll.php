<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 31.07.2017
 * Time: 19:48
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;


/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Poll")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Poll
{
    const TYPE_ONEANSWER = 1;
    const TYPE_MULTIANSWER = 2;
    use SoftDeleteableEntity;
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    public $id;

    /**
     * @ORM\Column(type="string",length=255)
     */
    public $name;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    public $endDate;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    public $closed=false;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @var User
     */
    public $owner;

    /**
     * @ORM\Column(type="smallint")
     * @var
     */
    public $type;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    public $allowAdd;

    /**
     * @ORM\Column(type="smallint")
     */
    public $permission=0;

    /**
     * @var PollAnswer[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\PollAnswer",mappedBy="poll",cascade={"persist","remove"})
     */
    protected $answers;

    /**
     * @var Comment[]
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Comment")
     * @ORM\OrderBy({"created" = "ASC"})
     */
    public $comments;


    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    public $created;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    public $updated;


    /**
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    public $createdBy;

    /**
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    public $updatedBy;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    public $info;


    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->answers = new ArrayCollection();
        $this->answers->add(new PollAnswer());
        $this->endDate = new \DateTime('+6 weeks');
        $this->comments = new ArrayCollection();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdate()
    {
        $this->updated = new \DateTime();
    }
    public function isOpen(){

        if($this->closed){
            return false;
        }
        if($this->endDate < new \DateTime()){
            return false;
        }

        return true;
    }

    public function removeAnswer(PollAnswer $answer)
    {
        $this->answers->remove($answer);
        return $this;
    }
    /**
     * @return PollAnswer[]
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * @param PollAnswer[] $answers
     */
    public function setAnswers($answers)
    {
        foreach ($answers as $answer) {
            $answer->poll = $this;
            $this->answers->add($answer);
        }
    }

    public function isAnsweredByUser(User $user){
        foreach($this->getAnswers() as $answer){
            if($answer->getVoteForUser($user) instanceof UserVote){
                return true;
            }
        }
        return false;
    }

}
