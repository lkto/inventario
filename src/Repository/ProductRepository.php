<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 12/08/2019
 * Time: 11:40 AM
 */

namespace App\Repository;
use Doctrine\ORM\EntityRepository;

class ProductRepository extends  EntityRepository
{
    public function getProductDisable()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.stock <= 0' );

        return $qb->getQuery()->getResult();
    }

    public function getProductsNotStock()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.stock <= 10' );
        $qb->andWhere('p.stock > 0');


        return $qb->getQuery()->getResult();
    }

    public function getEnableProduct()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.stock > 0' );
        return $qb->getQuery()->getResult();
    }

}