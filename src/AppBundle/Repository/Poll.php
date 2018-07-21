<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28.07.2017
 * Time: 20:45
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class Poll extends EntityRepository
{

    /**
     * @return \AppBundle\Entity\Poll[]
     */
    public function findAllOrdered($permission=0)
    {
        $query = $this->createQueryBuilder('p')
            ->addSelect('CASE WHEN p.endDate <= NOW() THEN 1 ELSE 0 END as HIDDEN overdue');
        if($permission < 1) {
            $query
                ->andWhere('p.permission = 0');
        };
        $query
            ->addOrderBy('p.closed')
            ->addOrderBy('overdue')
            ->addOrderBy('p.created','DESC');
        return $query->getQuery()->execute();

    }

    /**
     * @return \AppBundle\Entity\Poll[]
     */
    public function findOpen()
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.closed = :closed')
            ->andWhere('p.endDate > NOW()')
            ->setParameter('closed',false)
            ->orderBy('p.created');
        return $query->getQuery()->execute();

    }

    /**
     * @return \AppBundle\Entity\Poll[]
     */
    public function findTopFive($permission=0)
    {
        $query = $this->createQueryBuilder('p')
            ->where('p.closed = :closed')
            ->andWhere('p.endDate > NOW()');
        if($permission < 1) {
            $query
                ->andWhere('p.permission = 0');
        }
        $query
            ->setParameter('closed',false)
            ->orderBy('p.created','DESC')
            ->setMaxResults(5);
        return $query->getQuery()->execute();

    }

}