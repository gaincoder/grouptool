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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Photoalbum")
 */
class Photoalbum
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
    public $name;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Photo",mappedBy="album")
     */
    public $photos;

    /**
     * @ORM\Column(type="smallint")
     */
    public $permission=0;



}