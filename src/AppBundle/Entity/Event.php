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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Event")
 * @ORM\HasLifecycleCallbacks()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Event
{
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
    public $date;

    /**
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    public $location;

    /**
     * @ORM\Column(type="text",nullable=true)
     */
    public $info;

    /**
     * @ORM\Column(type="smallint")
     */
    public $permission=0;

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
     * @var EventVote[]
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\EventVote",mappedBy="event",cascade={"persist","remove"})
     */
    public $votes;


    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @var User
     */
    public $owner;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    public $disableImpulse;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    public $maxPersons = 0;


    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    public $public;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdate()
    {
        $this->updated = new \DateTime();
    }

    public function toStdClass()
    {
        $obj = new \stdClass();
        $obj->id = $this->id;
        $obj->name = $this->name;
        $obj->date = $this->date->format(DATE_RFC3339);
        $obj->location = $this->location;
        $obj->public = $this->public;
        return $obj;
    }
}