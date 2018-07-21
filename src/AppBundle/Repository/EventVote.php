<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28.07.2017
 * Time: 20:45
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class EventVote extends EntityRepository
{

    /**
     * @return \AppBundle\Entity\EventVote
     */
    public function getForEventAndUser($event,$user)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.event = :event')
            ->andWhere('e.user = :user')
            ->setParameter('event',$event)
            ->setParameter('user',$user);
        return $query->getQuery()->getOneOrNullResult();

    }

    /**
     * @return \AppBundle\Entity\EventVote[]
     */
    public function getForEventAndVote($event,$vote)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.event = :event')
            ->andWhere('e.vote = :vote')
            ->setParameter('event',$event)
            ->setParameter('vote',$vote);
        return $query->getQuery()->getResult();

    }
}