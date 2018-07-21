<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28.07.2017
 * Time: 20:45
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class PollAnswer extends EntityRepository
{

    /**
     * @return \AppBundle\Entity\PollAnswer[]
     */
    public function getOrderedForPoll($poll)
    {
        $query = $this->createQueryBuilder('p')
            ->select('p','count(v) AS HIDDEN myCount')
            ->leftJoin('p.votes','v')
            ->where('p.poll = :poll')
            ->setParameter('poll',$poll)
            ->groupBy('p.id')
            ->having('myCount > 0')
            ->addOrderBy('myCount','DESC');
        return $query->getQuery()->execute();

    }


}