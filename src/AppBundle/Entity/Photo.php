<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28.07.2017
 * Time: 20:41
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Photo
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

      /**
     * @ORM\Column(type="string",length=255)
     */
    public $thumbnail;

    /**
     * @ORM\Column(type="string",length=255)
     */
    public $photo;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Photoalbum",inversedBy="photos")
     */
    public $album;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    public $created;

    /**
     * @ORM\Column(type="smallint",options={"default":0})
     */
    public $type=0;


    public function __construct()
    {
        $this->created = new \DateTime();
    }

}