<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28.07.2017
 * Time: 20:45
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class Birthday extends EntityRepository
{

    /**
     * @return \AppBundle\Entity\Birthday[]
     */
    public function findAllOrderedByDay()
    {
        $query = $this->createQueryBuilder('b')
            ->orderBy('DATE_FORMAT(b.birthdate,\'2000-%m-%d\')');
        return $query->getQuery()->execute();

    }

    /**
     * @return \AppBundle\Entity\Birthday[]
     */
    public function findAllFutureOrderedByDay()
    {
        $year = date('Y');
        $query = $this->createQueryBuilder('b')
            ->where('DATE_FORMAT(b.birthdate,\''.$year.'%m%d\') >= DATE_FORMAT(NOW(),\'%Y%m%d\')')
            ->orderBy('DATE_FORMAT(b.birthdate,\'2000-%m-%d\')');
        return $query->getQuery()->execute();

    }

    /**
     * @return \AppBundle\Entity\Birthday[]
     */
    public function findAllPastOrderedByDay()
    {
        $year = date('Y');
        $query = $this->createQueryBuilder('b')
            ->where('DATE_FORMAT(b.birthdate,\''.$year.'%m%d\') < DATE_FORMAT(NOW(),\'%Y%m%d\')')
            ->orderBy('DATE_FORMAT(b.birthdate,\'2000-%m-%d\')');
        return $query->getQuery()->execute();

    }

    /**
     * @return \AppBundle\Entity\Birthday[]
     */
    public function findAllThisMonthOrderedByDay()
    {
        $month = date('m');
        $query = $this->createQueryBuilder('b')
            ->where('DATE_FORMAT(b.birthdate,\'%m\') = \''.$month.'\'')
            ->orderBy('DATE_FORMAT(b.birthdate,\'2000-%m-%d\')');
        return $query->getQuery()->execute();

    }

    /**
     * @return \AppBundle\Entity\Birthday[]
     */
    public function findAllNextMonthOrderedByDay()
    {
        $month = date('m',strtotime('next month'));
        $query = $this->createQueryBuilder('b')
            ->where('DATE_FORMAT(b.birthdate,\'%m\') = \''.$month.'\'')
            ->orderBy('DATE_FORMAT(b.birthdate,\'2000-%m-%d\')');
        return $query->getQuery()->execute();

    }

    /**
     * @return \AppBundle\Entity\Birthday[]
     */
    public function findToday()
    {
        $year = date('Y');
        $query = $this->createQueryBuilder('b')
            ->where('DATE_FORMAT(b.birthdate,\''.$year.'-%m-%d\') = :today')
            ->setParameter('today',date('Y-m-d'));
        return $query->getQuery()->execute();

    }

    /**
     * @return \AppBundle\Entity\Birthday[]
     */
    public function findInTwoWeeks()
    {
        $year = date('Y');
        $query = $this->createQueryBuilder('b')
            ->where('DATE_FORMAT(b.birthdate,\''.$year.'-%m-%d\') = :inTwoWeeks')
            ->setParameter('inTwoWeeks',date('Y-m-d',strtotime('+2 weeks')));
        return $query->getQuery()->execute();

    }
}