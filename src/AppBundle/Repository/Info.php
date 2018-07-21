<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 28.07.2017
 * Time: 20:45
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class Info extends EntityRepository
{

    /**
     * @return \AppBundle\Entity\Info[]
     */
    public function findAllOrdered($permission=0)
    {
        $query = $this->createQueryBuilder('i');
        if($permission < 1) {
            $query
                ->andWhere('i.permission = 0');
        };
        $query
            ->addOrderBy('i.important','DESC');
        return $query->getQuery()->execute();

    }



}