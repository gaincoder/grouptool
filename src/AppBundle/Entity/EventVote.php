<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 16.08.2017
 * Time: 20:07
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventVote")
 */
class EventVote
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    public $id;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    public $vote;

    /**
     * @var Poll
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event",inversedBy="votes")
     */
    public $event;

    /**
     * @var Poll
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    public $user;
}