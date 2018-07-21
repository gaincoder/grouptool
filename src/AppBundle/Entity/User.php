<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 24.07.2017
 * Time: 14:49
 */

namespace AppBundle\Entity;


use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\HasLifecycleCallbacks()
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    public $telegramUsername;


    /**
     * @ORM\Column(type="string",length=255,nullable=true)
     */
    public $telegramChatId;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    public function setUsername($username)
    {
        $this->setEmail($username . '@localhost.dev');
        $this->setEmailCanonical($username . '@localhost.dev');
        return parent::setUsername($username);
    }

    /**
     * @ORM\PrePersist
     */
    public function disable(){
        $this->setEnabled(false);
    }

    public function telegramSupported()
    {
        if(strlen((string)$this->telegramChatId) > 0){
            return true;
        }else{
            return false;
        }
    }

}