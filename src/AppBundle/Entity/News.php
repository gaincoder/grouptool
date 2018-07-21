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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\News")
 */
class News
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
    public $headline;

    /**
     * @ORM\Column(type="text")
     */
    public $text;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    public $date;

    /**
     * @ORM\Column(type="smallint")
     */
    public $permission=0;

    /**
     * News constructor.
     * @param $headline
     * @param $text
     * @param \DateTime $date
     */
    public function __construct($headline, $text,$permission=0)
    {
        $this->headline = $headline;
        $this->text = $text;
        $this->permission = $permission;
        $this->date = new \DateTime();
    }


}