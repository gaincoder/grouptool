<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28.07.2017
 * Time: 20:45
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class Photoalbum extends EntityRepository
{



    public function findAllOrdered($permission=0)
    {
        $query = $this->createQueryBuilder('e');
        if($permission < 1) {
            $query
                ->andWhere('e.permission = 0');
        }
        $query->orderBy('e.id','DESC');
        return $query->getQuery()->execute();

    }

}