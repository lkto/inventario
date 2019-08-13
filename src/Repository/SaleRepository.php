<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 13/08/2019
 * Time: 1:15 PM
 */

namespace App\Repository;
use Doctrine\ORM\EntityRepository;


class SaleRepository extends  EntityRepository
{
    public function getFinallyRegister(){
        $qb = $this->createQueryBuilder('s');
        $qb->setMaxResults(1);
        $qb->orderBy('s.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

}