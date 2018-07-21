<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 16.08.2017
 * Time: 20:07
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PollAnswer")
 */
class PollAnswer
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    public $id;

    /**
     * @ORM\Column(type="string",length=255)
     */
    public $label;

    /**
     * @var Poll
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Poll",inversedBy="answers")
     */
    public $poll;

    /**
     * @var UserVote[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserVote",mappedBy="answer",cascade={"persist","remove"})
     */
    public $votes;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

    /**
     * @param User $user
     * @return UserVote
     */
    public function getVoteForUser(User $user)
    {
       $criteria = Criteria::create()->where(Criteria::expr()->eq('user',$user));
       return $this->votes->matching($criteria)->first();
    }

    /**
     * @param User $user
     * @return UserVote[]
     */
    public function getVotesForVote($vote)
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq('vote',$vote));
        return $this->votes->matching($criteria);
    }
}