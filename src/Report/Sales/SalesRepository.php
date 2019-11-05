<?php

namespace App\Report\Sales;

use Doctrine\DBAL\Driver\Connection;

class SalesRepository
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
     * SalesRepository constructor.
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
     * @return type
     */
    public function getReportBase($regionId = null, $subRegionId = null, $businessUnitId = null, $grouping = null)
    {

        $query = '
                SELECT
                    @selectFields
                    0 as expected_revenue,
                    0 as expected_points,
                    0 as expected_ytd_revenue,
                    0 as expected_ytd_points,
                    0 as uploaded_revenue,
                    0 as uploaded_revenue_percentaje,
                    0 as uploaded_points,
                    0 as uploaded_points_percentaje
                FROM
                    region
                    INNER JOIN region_subregion ON region_subregion.region_id = region.id
                    INNER JOIN sub_region ON region_subregion.sub_region_id = sub_region.id
                    CROSS JOIN business_unit
                WHERE
                    @whereClause
                GROUP BY
                    @groupBy
                ORDER BY
                    region_name,
                    sub_region_name,
                    business_unit_name
        ';

        // Grouping Section And Select Fields
        switch ($grouping)
        {
            case 'subregion':

                $groupBy = 'region_id, sub_region_id';

                $selectFields =    'region.id as region_id,
                                    sub_region.id as sub_region_id,
                                    0 as business_unit_id,
                                    region.`name` as region_name,
                                    sub_region.`name` as sub_region_name,
                                    "All" as business_unit_name,';

                break;

            case 'business_unit':

                $groupBy = 'region_id, sub_region_id, business_unit_id';

                $selectFields = 'region.id as region_id,
                                    sub_region.id as sub_region_id,
                                    business_unit.id as business_unit_id,
                                    region.`name` as region_name,
                                    sub_region.`name` as sub_region_name,
                                    business_unit.`name` as business_unit_name,';

                break;

            default:

                $groupBy = 'region_id';

                $selectFields =    'region.id as region_id,
                                    0 as sub_region_id,
                                    0 as business_unit_id,
                                    region.`name` as region_name,
                                    "All" as sub_region_name,
                                    "All" as business_unit_name,';

                break;
        }

        // Where Clause Section
        $whereClause = '1 AND business_unit.parent_id is not null ';

        if(!empty($regionId))       $whereClause .= ' AND region.id = ' . $regionId;
        if(!empty($subRegionId))    $whereClause .= ' AND sub_region.id = ' . $subRegionId;
        if(!empty($businessUnitId)) $whereClause .= ' AND business_unit.id = ' . $businessUnitId;

        // Final Query Construction
        $query = str_replace(array('@selectFields','@whereClause','@groupBy'), array($selectFields,$whereClause,$groupBy), $query);

        // dump($query);die;

        $stmt = $this->defaultConn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;

    }

    /**
     *
     * @return type
     */

    public function getSalesGoals($regionId = null, $subRegionId = null, $businessUnitId = null, $grouping = null)
    {

        $query = '
                    SELECT
                        @selectFields
                        ROUND(SUM(company_goal.goal_amount),0) AS expected_revenue,
                        ROUND(SUM(company_goal.goal_amount*0.15),0) AS expected_points,
                        ROUND((SUM(company_goal.goal_amount) / TIMESTAMPDIFF(WEEK,period.start_sale,period.end_sale )) * TIMESTAMPDIFF(WEEK, period.start_sale, now()),0) AS expected_ytd_revenue,
                        ROUND((SUM(company_goal.goal_amount*0.15) / TIMESTAMPDIFF(WEEK,period.start_sale,period.end_sale)) * TIMESTAMPDIFF(WEEK, period.start_sale, now()),0) AS expected_ytd_points
                    FROM
                        company_goal
                        INNER JOIN company_goal_bu ON company_goal_bu.company_goal_id = company_goal.id
                        INNER JOIN company ON company_goal.company_id = company.id
                        INNER JOIN country ON company.country_id = country.id
                        INNER JOIN sub_region_country ON sub_region_country.country_id = country.id
                        INNER JOIN region_subregion ON sub_region_country.subregion_id = region_subregion.sub_region_id
                        INNER JOIN region ON region_subregion.region_id = region.id
                        INNER JOIN quarter_month ON company_goal.quarter_month_id = quarter_month.id
                        INNER JOIN `quarter` ON quarter_month.quarter_id = `quarter`.id
                        INNER JOIN period ON `quarter`.period_id = period.id
                    WHERE
                        @whereClause
                    GROUP BY
                        @groupBy
        ';

        // Grouping Section And Select Fields
        switch ($grouping)
        {
            case 'subregion':
                $groupBy = 'region_id, sub_region_id';
                $selectFields =    'region.id as region_id,
                                    region_subregion.sub_region_id as sub_region_id,
                                    0 as bu_id,';
                break;
            case 'business_unit':
                $groupBy = 'region_id, sub_region_id, bu_id';
                $selectFields =    'region.id as region_id,
                                    region_subregion.sub_region_id as sub_region_id,
                                    company_goal_bu.sub_business_unit_id as bu_id,';
                break;
            default:
                $groupBy = 'region_id';
                $selectFields =    'region.id as region_id,
                                    0 as sub_region_id,
                                    0 as bu_id,';
                break;
        }

        // Where Clause Section
        $whereClause = '1 ';

        if(!empty($regionId))       $whereClause .= ' AND region.id = ' . $regionId;
        if(!empty($subRegionId))    $whereClause .= ' AND region_subregion.sub_region_id = ' . $subRegionId;
        if(!empty($businessUnitId)) $whereClause .= ' AND company_goal_bu.sub_business_unit_id = ' . $businessUnitId;

        // Final Query Construction
        $query = str_replace(array('@selectFields','@whereClause','@groupBy'), array($selectFields,$whereClause,$groupBy), $query);

        // dump($query);die;

        $stmt = $this->defaultConn->prepare($query);
        $stmt->execute();
        $tmpResult = $stmt->fetchAll();

        $result = array();

        // Creating and asociative array for easy reference
        foreach ($tmpResult as $key => $value)
        {
            //Create the Key (RegionId + SubRegionId + BusinessUnitId)
            $arrayKey = 'RE' . $value['region_id'] . 'SR' . $value['sub_region_id'] . 'BU' . $value['bu_id'];
            $result[$arrayKey] = array(
                                        'expected_revenue' => $value['expected_revenue'],
                                        'expected_points' => $value['expected_points'],
                                        'expected_ytd_revenue' => $value['expected_ytd_revenue'],
                                        'expected_ytd_points' => $value['expected_ytd_points']
                                      );
        }

        //var_dump($result);die;

        return $result;

    }

    /**
     *
     * @return type
     */

    public function getSalesUploaded($regionId = null, $subRegionId = null, $businessUnitId = null, $grouping = null)
    {

        $query = '
                    SELECT
                        @selectFields
                        ,SUM( fact_sales_current.m_reported_revenue ) as uploaded_revenue
                        ,SUM( fact_sales_current.m_points ) as uploaded_points
                    FROM
                        fact_sales as fact_sales_current
                        INNER JOIN dim_business_unit ON fact_sales_current.business_unit_sk = dim_business_unit.dim_business_unit_sk
                        LEFT JOIN
                        (
                            SELECT
                            dim_company.dim_company_region_id,
                            dim_company.dim_company_subregion_id,
                            IF(UPPER(dim_company.dim_company_region) = "BRAZIL","BRASIL",dim_company.dim_company_region) as dim_company_region,
                            IF(UPPER(dim_company.dim_company_subregion) = "BRAZIL","Brasil",dim_company.dim_company_subregion) as dim_company_subregion
                            FROM
                            dim_company
                            WHERE
                            dim_company.dim_company_region_id NOT IN (-1,0) AND dim_company.dim_company_subregion_id NOT IN (-1,0)
                            GROUP BY
                            dim_company.dim_company_region_id,
                            dim_company.dim_company_region,
                            dim_company.dim_company_subregion_id,
                            dim_company.dim_company_subregion

                        ) as regionsTMP ON regionsTMP.dim_company_region = fact_sales_current.reported_region AND regionsTMP.dim_company_subregion = fact_sales_current.reported_subregion
                    WHERE
                        @whereClause
                    GROUP BY
                        @groupBy
        ';

        // Grouping Section And Select Fields
        switch ($grouping)
        {
            case 'subregion':
                $groupBy = 'region_id, sub_region_id';
                $selectFields =    'regionsTMP.dim_company_region_id as region_id
                                    ,regionsTMP.dim_company_subregion_id as sub_region_id
                                    ,0 as business_unit_id';
                break;
            case 'business_unit':
                $groupBy = 'region_id, sub_region_id, business_unit_id';
                $selectFields =    'regionsTMP.dim_company_region_id as region_id
                                    ,regionsTMP.dim_company_subregion_id as sub_region_id
                                    ,dim_business_unit.dim_business_unit_id as business_unit_id';
                break;
            default:
                $groupBy = 'region_id';
                $selectFields =    'regionsTMP.dim_company_region_id as region_id
                                    ,0 as sub_region_id
                                    ,0 as business_unit_id';
                break;
        }

        // Where Clause Section
        $whereClause = '1 AND fact_sales_current.transaction_status IN ("Calculated","Transferred") AND fact_sales_current.transfer_status = "Transferred"';

        if(!empty($regionId))       $whereClause .= ' AND regionsTMP.dim_company_region_id = ' . $regionId;
        if(!empty($subRegionId))    $whereClause .= ' AND regionsTMP.dim_company_subregion_id = ' . $subRegionId;
        if(!empty($businessUnitId)) $whereClause .= ' AND dim_business_unit.dim_business_unit_id = ' . $businessUnitId;

        // Final Query Construction
        $query = str_replace(array('@selectFields','@whereClause','@groupBy'), array($selectFields,$whereClause,$groupBy), $query);

        // dump($query);die;

        $stmt = $this->dwConn->prepare($query);
        $stmt->execute();
        $tmpResult = $stmt->fetchAll();

        $result = array();

        // Creating and asociative array for easy reference
        foreach ($tmpResult as $key => $value)
        {
            //Create the Key (RegionId + SubRegionId + BusinessUnitId)
            $arrayKey = 'RE' . $value['region_id'] . 'SR' . $value['sub_region_id'] . 'BU' . $value['business_unit_id'];
            $result[$arrayKey] = array(
                                        'uploaded_revenue' => $value['uploaded_revenue'],
                                        'uploaded_points' => $value['uploaded_points']
                                      );
        }

        // var_dump($result);die;

        return $result;
    }

    /**
     *
     * @return type
     */
    public function getRegionSales($regionId = null, $subRegionId = null, $businessUnitId = null, $grouping = null)
    {
        // Get all Available Regions
        $reportBase = $this->getReportBase($regionId, $subRegionId, $businessUnitId, $grouping);

        // Get all Goals
        $goals = $this->getSalesGoals($regionId, $subRegionId, $businessUnitId, $grouping);

        // Get all Sales
        $sales = $this->getSalesUploaded($regionId, $subRegionId, $businessUnitId, $grouping);

        // Final construction of the array
        foreach ($reportBase as $key => $value)
        {
            // Building key to search in the indexed arrays $goals and $sales
            // Key (RegionId + SubRegionId + BusinessUnitId) Example 'RE1SR2BU3' => Region(1),SubRegion(2),BusinessUnit(3)
            $arrayKey = 'RE' . $value['region_id'] . 'SR' . $value['sub_region_id'] . 'BU' . $value['business_unit_id'];

            // Setting missing values for goals
            $reportBase[$key]['expected_revenue'] = (empty($goals[$arrayKey]['expected_revenue'])) ? 0 : $goals[$arrayKey]['expected_revenue'];
            $reportBase[$key]['expected_points'] = (empty($goals[$arrayKey]['expected_points'])) ? 0 : $goals[$arrayKey]['expected_points'];
            $reportBase[$key]['expected_ytd_revenue'] = (empty($goals[$arrayKey]['expected_ytd_revenue'])) ? 0 : $goals[$arrayKey]['expected_ytd_revenue'];
            $reportBase[$key]['expected_ytd_points'] = (empty($goals[$arrayKey]['expected_ytd_points'])) ? 0 : $goals[$arrayKey]['expected_ytd_points'];

            // Setting missing values for uploaded sales
            $reportBase[$key]['uploaded_revenue'] = (empty($sales[$arrayKey]['uploaded_revenue'])) ? 0 : $sales[$arrayKey]['uploaded_revenue'];
            $reportBase[$key]['uploaded_revenue_percentaje'] = (empty($sales[$arrayKey]['expected_revenue']) || empty($sales[$arrayKey]['uploaded_revenue'])) ? 0 : ($reportBase[$key]['uploaded_revenue'] / $reportBase[$key]['expected_revenue']) * 100;
            $reportBase[$key]['uploaded_points'] = (empty($sales[$arrayKey]['uploaded_points'])) ? 0 : $sales[$arrayKey]['uploaded_points'];
            $reportBase[$key]['uploaded_points_percentaje'] = (empty($sales[$arrayKey]['expected_points']) || empty($sales[$arrayKey]['uploaded_points'])) ? 0 : ($reportBase[$key]['uploaded_points'] / $reportBase[$key]['expected_points']) * 100;;

        }

        //var_dump($reportBase);die;

        return $reportBase;
    }
}
