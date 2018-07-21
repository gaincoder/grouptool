<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28.07.2017
 * Time: 20:41
 */

namespace AppBundle\Entity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Birthday")
 */
class Birthday
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(type="date")
     * @var DateTime
     */
    public $birthdate;


    /**
     * @ORM\Column(type="name",type="string",length=100)
     */
    public $name;

    public function getNextAge()
    {
        $nextBirthday = $this->getNextBirthday()->setTime(2,0,0);
        $diff = $this->birthdate->diff($nextBirthday);
        return $diff->y;
    }

    public function getAgeThisYear()
    {
        $nextBirthday = $this->getBirthdayThisYear()->setTime(2,0,0);
        $diff = $this->birthdate->diff($nextBirthday);
        return $diff->y;
    }

    public function thisYearBirthdayIsRound()
    {
        $nextAge = $this->getAgeThisYear();
        $quotient = $nextAge / 10;
        return $quotient == round($quotient);
    }

    public function nextBirthdayIsRound()
    {
        $nextAge = $this->getNextAge();
        $quotient = $nextAge / 10;
        return $quotient == round($quotient);
    }

    protected function getNextBirthday()
    {
        $birthday = $this->getBirthdayThisYear();
        $today = $this->getToday();
        if($birthday < $today){
            $birthday = $this->getBirthdayNextYear();
        }
        return $birthday;
    }

    protected function getBirthdayThisYear()
    {
        $year = date('Y');
        return $this->getBirthdayForYear($year);
    }
    protected function getBirthdayNextYear()
    {
        $year = date('Y',strtotime('next year'));
        return $this->getBirthdayForYear($year);
    }

    protected function getBirthdayForYear($year)
    {
        $birthday = new DateTime($year.'-'.$this->birthdate->format('m-d'));
        $birthday->setTime(0,0,0);
        return $birthday;
    }

    protected function getToday()
    {
        $today = new DateTime();
        $today->setTime(0,0,0);
        return $today;
    }

}