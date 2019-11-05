<?php

/**
 * Created by apetit on 29/7/2017.
 */

namespace App\Report\Point;

use Doctrine\DBAL\Driver\Connection;

class PointRepository
{
    /**
     * @var Connection
     */
    private $dwConn;

    /**
     * @var Connection
     */
    private $defaultConn;

    /**
     * PointRepository constructor.
     *
     * @param Connection $dwConn
     * @param Connection $defaultConn
     */
    public function __construct(Connection $dwConn, Connection $defaultConn)
    {
        $this->dwConn = $dwConn;
        $this->defaultConn = $defaultConn;
    }

    /**
     *
     * @param type $region
     * @return type
     */
    public function getPointsAllRegions($region, $subregion, $country)
    {
        $position = 0;

        $query = "
            select
                dim_company.dim_company_region as company_region,
                sum(if (fact_points.transaction_type_sk = 1, fact_points.points, 0)) as company_credit,
                sum(if (fact_points.transaction_type_sk = 2, fact_points.points, 0)) as company_debit,
                sum(if ((fact_points.transaction_type_sk = 1 and fact_points.point_type_sk = 1), fact_points.points, 0)) as company_credit_sales,
                sum(if ((fact_points.transaction_type_sk = 1 and fact_points.point_type_sk <> 1), fact_points.points, 0)) as company_credit_behaviors,
                sum(if (fact_points.transaction_type_sk = 3, fact_points.points, 0)) as user_credit,
                sum(if (fact_points.transaction_type_sk = 4, fact_points.points, 0)) as user_debit,
                sum(if (fact_points.transaction_type_sk = 3 and fact_points.activated_indicator = 'Active', fact_points.points, 0)) as activated
            from fact_points
                inner join dim_company on fact_points.company_sk = dim_company.dim_company_sk
            where
                fact_points.point_status = 'Active' and
                dim_company.dim_company_demo = 'Regular' and
                dim_company.dim_company_type in ('Distributor' , 'Partner') and
                dim_company.dim_company_status = 'Active'
        ";

        if (!empty($region))
        {
            $query .= " and dim_company.dim_company_region_id = ?";
        }

        if (!empty($subregion))
        {
            $query .= " and dim_company.dim_company_subregion_id = ?";
        }

        if (!empty($country))
        {
            $query .= " and dim_company.dim_company_country_id = ?";
        }

        $query .= "
            group by
                dim_company.dim_company_region
            order by
                dim_company.dim_company_region;
        ";

        $stmt = $this->dwConn->prepare($query);

        if (!empty($region))
        {
            $position += 1;

            $stmt->bindValue($position, $region);
        }

        if (!empty($subregion))
        {
            $position += 1;

            $stmt->bindValue($position, $subregion);
        }

        if (!empty($country))
        {
            $position += 1;

            $stmt->bindValue($position, $country);
        }

        $stmt->execute();

        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     *
     * @param type $subregion
     * @return type
     */
    public function getPointsAllSubregions($region, $subregion, $country)
    {
        $position = 0;

        $query = "
            select
                dim_company.dim_company_region as company_region,
                dim_company.dim_company_subregion as company_subregion,
                sum(if (fact_points.transaction_type_sk = 1, fact_points.points, 0)) as company_credit,
                sum(if (fact_points.transaction_type_sk = 2, fact_points.points, 0)) as company_debit,
                sum(if ((fact_points.transaction_type_sk = 1 and fact_points.point_type_sk = 1), fact_points.points, 0)) as company_credit_sales,
                sum(if ((fact_points.transaction_type_sk = 1 and fact_points.point_type_sk <> 1), fact_points.points, 0)) as company_credit_behaviors,
                sum(if (fact_points.transaction_type_sk = 3, fact_points.points, 0)) as user_credit,
                sum(if (fact_points.transaction_type_sk = 4, fact_points.points, 0)) as user_debit,
                sum(if (fact_points.transaction_type_sk = 3 and fact_points.activated_indicator = 'Active', fact_points.points, 0)) as activated
            from fact_points
                inner join dim_company on fact_points.company_sk = dim_company.dim_company_sk
            where
                fact_points.point_status = 'Active' and
                dim_company.dim_company_demo = 'Regular' and
                dim_company.dim_company_type in ('Distributor' , 'Partner') and
                dim_company.dim_company_status = 'Active'
        ";

        if (!empty($region))
        {
            $query .= " and dim_company.dim_company_region_id = ?";
        }

        if (!empty($subregion))
        {
            $query .= " and dim_company.dim_company_subregion_id = ?";
        }

        if (!empty($country))
        {
            $query .= " and dim_company.dim_company_country_id = ?";
        }

        $query .= "
            group by
                dim_company.dim_company_subregion
            order by
                dim_company.dim_company_subregion;
        ";

        $stmt = $this->dwConn->prepare($query);

        if (!empty($region))
        {
            $position += 1;

            $stmt->bindValue($position, $region);
        }

        if (!empty($subregion))
        {
            $position += 1;

            $stmt->bindValue($position, $subregion);
        }

        if (!empty($country))
        {
            $position += 1;

            $stmt->bindValue($position, $country);
        }

        $stmt->execute();

        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     *
     * @param type $country
     * @return type
     */
    public function getPointsAllCountries($region, $subregion, $country)
    {
        $position = 0;

        $query = "
            select
                dim_company.dim_company_region as company_region,
                dim_company.dim_company_subregion as company_subregion,
                dim_company.dim_company_country as company_country,
                sum(if (fact_points.transaction_type_sk = 1, fact_points.points, 0)) as company_credit,
                sum(if (fact_points.transaction_type_sk = 2, fact_points.points, 0)) as company_debit,
                sum(if ((fact_points.transaction_type_sk = 1 and fact_points.point_type_sk = 1), fact_points.points, 0)) as company_credit_sales,
                sum(if ((fact_points.transaction_type_sk = 1 and fact_points.point_type_sk <> 1), fact_points.points, 0)) as company_credit_behaviors,
                sum(if (fact_points.transaction_type_sk = 3, fact_points.points, 0)) as user_credit,
                sum(if (fact_points.transaction_type_sk = 4, fact_points.points, 0)) as user_debit,
                sum(if (fact_points.transaction_type_sk = 3 and fact_points.activated_indicator = 'Active', fact_points.points, 0)) as activated
            from fact_points
                inner join dim_company on fact_points.company_sk = dim_company.dim_company_sk
            where
                fact_points.point_status = 'Active' and
                dim_company.dim_company_demo = 'Regular' and
                dim_company.dim_company_type in ('Distributor' , 'Partner') and
                dim_company.dim_company_status = 'Active'
        ";

        if (!empty($region))
        {
            $query .= " and dim_company.dim_company_region_id = ?";
        }

        if (!empty($subregion))
        {
            $query .= " and dim_company.dim_company_subregion_id = ?";
        }

        if (!empty($country))
        {
            $query .= " and dim_company.dim_company_country_id = ?";
        }

        $query .= "
            group by
                dim_company.dim_company_country
            order by
                dim_company.dim_company_country
        ";

        $stmt = $this->dwConn->prepare($query);

        if (!empty($region))
        {
            $position += 1;

            $stmt->bindValue($position, $region);
        }

        if (!empty($subregion))
        {
            $position += 1;

            $stmt->bindValue($position, $subregion);
        }

        if (!empty($country))
        {
            $position += 1;

            $stmt->bindValue($position, $country);
        }

        $stmt->execute();

        $result = $stmt->fetchAll();

        return $result;
    }
}
