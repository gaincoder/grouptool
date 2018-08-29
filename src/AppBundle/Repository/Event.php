<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28.07.2017
 * Time: 20:45
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class Event extends EntityRepository
{



    public function findFuture($permission=0)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.date >= NOW()');
        if($permission < 1) {
            $query
                ->andWhere('e.permission = 0');
        }
        $query
            ->orderBy('e.date');
        return $query->getQuery()->execute();

    }

    /**
     * @return \AppBundle\Entity\Event[]
     */
    public function findNextFive($permission=0)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.date >= NOW()');
        if($permission < 1) {
            $query
                ->andWhere('e.permission = 0');
        }
        $query
            ->orderBy('e.date')
            ->setMaxResults(5);
        return $query->getQuery()->execute();

    }


    /**
     * @return \AppBundle\Entity\Event[]
     */
    public function findNextThree()
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.date >= NOW()')
            ->andWhere('e.permission = 0')
            ->orderBy('e.date')
            ->setMaxResults(3);
        return $query->getQuery()->execute();

    }


    /**
     * @return \AppBundle\Entity\Event[]
     */
    public function findNextFiveForGroup($permission=0)
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.date >= NOW()')
            ->andWhere('e.public = 0');
        if($permission < 1) {
            $query
                ->andWhere('e.permission = 0');
        }
        $query
            ->orderBy('e.date')
            ->setMaxResults(5);
        return $query->getQuery()->execute();

    }

}