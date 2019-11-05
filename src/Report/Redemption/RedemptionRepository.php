<?php
/**
 * User: Cesar Lizarraga
 * Date: 2017-07-26
 * Time: 05:54 PM
 */

namespace App\Report\Redemption;

use Doctrine\DBAL\Driver\Connection;
use App\Entity\Profile;
use App\Entity\CompanyType;

class RedemptionRepository
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
     * RedemptionRepository constructor.
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
     * Preparing the data to be used.
     *
     * @param $regionId
     * @param $companyLevelId
     */
    public function getFormatedRedemptionDashboard($regionId = null, $companyLevelId = null)
    {
        $data = $this->getRedemptionDashboard(false, $regionId, false, null, null, null, $companyLevelId);

        $dashboard = array();
        //Reordenamiento de la data a ser dispuesta en el dashboard
        foreach($data as $key => $value){
            if($value['dim_company_type_id'] == CompanyType::TYPE_DISTRIBUTOR) {
                $dashboard[$value['dim_company_region']]['Distributors'] = $value;
            }elseif ($value['dim_company_type_id'] == CompanyType::TYPE_PARTNER) {
                $dashboard[$value['dim_company_region']]['Partners'] = $value;
            }
        }

        return $dashboard;
    }

    public function getRedemptionDashboard($total = false, $region = null, $detail = false, $groupBy=null, $companyType = null, $subregion = null, $companyLevel=null)
    {
        $query = "
SELECT
            region.id as dim_company_region_id,
            region.`name` as dim_company_region,
            sub_region.`name` as dim_company_subregion,
            sub_region.id as dim_company_subregion_id,
            company_type.id as dim_company_type_id,
            company_type.`name` as dim_company_type,
            company_level.`name` as dim_company_level,
            company_level.id as dim_company_level_id,
            SUM( redeemed.total_points ) as points_redeemed,
            COUNT( redeemed.id ) AS quantity_rewards,
            SUM( redeemed.total_points ) / COUNT( redeemed.id ) as ratio,
            COUNT( DISTINCT redeemed.user_id ) as users_redeeming,
            SUM(NETWORKDAYS( redeemed.preapproved_date, IF( redeemed.`status` = \"DELIVERED\", redeemed.delivery_date, NOW() ) ) ) as turnaround_sum,
            COUNT( NETWORKDAYS( redeemed.preapproved_date, IF( redeemed.`status` = \"DELIVERED\", redeemed.delivery_date, NOW() ) ) ) as turnaround_count,
            AVG(NETWORKDAYS( redeemed.preapproved_date, IF( redeemed.`status` = \"DELIVERED\", redeemed.delivery_date, NOW() ) )) AS turnaround
        FROM
            redeemed
            INNER JOIN catalog ON catalog.id = redeemed.catalog_id
            INNER JOIN company ON company.id = redeemed.company_id
            INNER JOIN country ON company.country_id = country.id
            LEFT JOIN company_level_relation ON company.id = company_level_relation.company_id
            LEFT JOIN company_level ON company_level_relation.company_level_id = company_level.id AND company_level_relation.active = 1
            LEFT JOIN company_subtype_relation ON company.id = company_subtype_relation.company_id
            LEFT JOIN company_subtype ON company_subtype_relation.company_subtype_id = company_subtype.id AND company_subtype_relation.active = 1
            LEFT JOIN company_type ON company_subtype.company_type_id = company_type.id
            LEFT JOIN sub_region_country ON sub_region_country.country_id = country.id
            LEFT JOIN region_subregion ON sub_region_country.subregion_id = region_subregion.sub_region_id
            LEFT JOIN region ON region_subregion.region_id = region.id
            LEFT JOIN sub_region ON sub_region.id = region_subregion.sub_region_id
        WHERE
            redeemed.`status` <> 'REJECTED'
            AND company.is_demo <> 1";

        if(!empty($region)){
            $query .= " AND region.id = :region ";
        }
        if(!empty($companyType)){
            $query .= " AND company_type.id = :companyType";
        }
        if(!empty($companyLevel)){
            $query .= " AND company_level.id = :companyLevel";
        }
        if(!empty($subregion)){
            $query .= " AND sub_region.id = :subregion";
        }

        if(is_null($groupBy)) {
            $query .= ' GROUP BY dim_company_region_id, dim_company_type_id';
        }elseif($groupBy == 'subregion') {
            $query .= ' GROUP BY dim_company_region_id, dim_company_type_id, dim_company_subregion_id';
                }elseif($groupBy == 'company-level') {
            $query .= ' GROUP BY dim_company_region_id, dim_company_type_id, dim_company_subregion_id, dim_company_level_id';
        }

        $query .= ' ORDER BY             
                        dim_company_region,
                        dim_company_subregion,
                        dim_company_type';

        //dump($query);die;

        $stmt = $this->defaultConn->prepare($query);
        if(!empty($region)){
            $stmt->bindValue('region', $region);
        }
        if(!empty($companyType)){
            $stmt->bindValue('companyType', $companyType);
        }
        if(!empty($companyLevel)){
            $stmt->bindValue('companyLevel', $companyLevel);
        }
        if(!empty($subregion)){
            $stmt->bindValue('subregion', $subregion);
        }
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getRedemptionDashboardTotal($data)
    {
        $totals = [];

        foreach ($data as $col => $regionData) {

            foreach ($regionData as $col => $row) {

                $totals[$col] = 0;

            }
        }

        foreach ($data as $regionData) {

            foreach ($regionData as $col => $value) {

                $totals[$col] += $value;

            }
        }

        // Patch for the totals
        if(!empty($totals))
        {
            $totals['ratio'] = $totals['points_redeemed'] / $totals['quantity_rewards'];
            $totals['turnaround'] = $totals['turnaround_sum'] / $totals['turnaround_count'];            
        }

        return $totals;
    }

    /**
     * Redemption Performance Report Live Xls
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRedemptionPerformanceLiveData($regionId=null, $companyLevelId=null)
    {
        $whereClause = "";

        if($regionId) $whereClause .= " AND region.id = :regionId";
        if($companyLevelId) $whereClause .= " AND company_level.id = :companyLevelId";

        $query = "
SELECT
                        region.`name` as company_region,
                        sub_region.`name` as company_subregion,
                        company.`name` as company_name,
                        company_type.`name` as company_type,
                        company_level.`name` as company_level,
                        person.firstname as user_first_name,
                        person.lastname as user_last_name,
                        'Unknown' as user_profile,
                        person.document_number as user_document_identity,
                        IF(`user`.enabled = 1, 'Active', 'Inactive') as user_status,
                        person.birthday as user_birthday,
                        `user`.email as user_email,
                        redeemed.control_number as purchase_order_number,
                        redeemed.prize_title as purchase_prize,
                        redeemed.`status` as purchase_status,
                        TRIM(CONCAT(address.line_one,' ',address.line_two,' ',address.line_three)) as purchase_address,
                        state.`name` as purchase_state,
                        city.`name` as purchase_city,
                        address.postal_code as purchase_postal_code,
                        redeemed.total_points as points_redeemed,
                        redeemed.created_at as purchase_created_date,
                        redeemed.rejected_date as purchase_reject_date,
                        redeemed.preapproved_date as purchase_pre_approved_date,
                        redeemed.shipped_date as purchase_shipped_date,
                        redeemed.delivery_date as purchase_delivered_date,
                        NETWORKDAYS( redeemed.preapproved_date, IF( redeemed.`status` = 'DELIVERED', redeemed.delivery_date, NOW() ) ) AS purchase_turnaround,
                        redeemed.`comment` as purchase_comment,
                        redeemed.comment_requested as purchase_comment_requested,
                        redeemed.comment_delivered as purchase_comment_delivered
                    FROM
                        redeemed
                        LEFT JOIN company ON company.id = redeemed.company_id
                        LEFT JOIN country ON company.country_id = country.id
                        LEFT JOIN company_level_relation ON company.id = company_level_relation.company_id
                        LEFT JOIN company_level ON company_level_relation.company_level_id = company_level.id AND company_level_relation.active = 1
                        LEFT JOIN company_subtype_relation ON company.id = company_subtype_relation.company_id
                        LEFT JOIN company_subtype ON company_subtype_relation.company_subtype_id = company_subtype.id AND company_subtype_relation.active = 1
                        LEFT JOIN company_type ON company_subtype.company_type_id = company_type.id
                        LEFT JOIN sub_region_country ON sub_region_country.country_id = country.id
                        LEFT JOIN region_subregion ON sub_region_country.subregion_id = region_subregion.sub_region_id
                        LEFT JOIN region ON region_subregion.region_id = region.id
                        LEFT JOIN sub_region ON sub_region.id = region_subregion.sub_region_id
                        LEFT JOIN `user` ON redeemed.user_id = `user`.id
                        LEFT JOIN person ON `user`.person_id = person.id
                        LEFT JOIN address ON address.id = redeemed.address_id
                        LEFT JOIN state ON state.id = address.state_id
                        LEFT JOIN city ON city.id = address.city_id
                    WHERE
                        1
                        #redeemed.`status` <> 'REJECTED'
                        AND company.is_demo <> 1
                        $whereClause
                    ORDER BY
                        purchase_created_date DESC
        ";

        $stmt = $this->defaultConn->prepare($query);
        if($regionId) $stmt->bindValue('regionId', $regionId);
        if($companyLevelId) $stmt->bindValue('companyLevelId', $companyLevelId);
        $stmt->execute();
        $result = $stmt->fetchAll();

        // Removing HTML Tags, Entities and spaces
        foreach ($result as $key => $value) $result[$key]['purchase_comment'] = trim(strip_tags(html_entity_decode($value['purchase_comment'])));

        return $result;
    }

}
