<?php
/**
 * User: Alberto Patiño
 * Date: 19-08-2015
 * Time: 08:23 PM
 */

namespace App\Report\Registration;

use Doctrine\DBAL\Driver\Connection;
use App\Entity\Profile;
use App\Entity\Company;
use App\Entity\CompanyType;

class RegistrationRepository
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
     * RegistrationRepository constructor.
     *
     * @param Connection $dwConn
     * @param Connection $defaultConn
     */
    public function __construct(Connection $dwConn, Connection $defaultConn)
    {
        $this->dwConn = $dwConn;
        $this->defaultConn = $defaultConn;
    }

    public function getFormatedRegistrationDashboard()
    {
        $data = $this->getRegistrationDashboard();

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

    public function getRegistrationDashboard($total = false, $region = null, $detail = false, $groupBy=null, $companyType = null, $subregion = null)
    {
        $query = '
        SELECT
            dim_company.dim_company_region_id,
            dim_company.dim_company_region,
            dim_company.dim_company_subregion,
            dim_company.dim_company_subregion_id,
            dim_company.dim_company_type_id,
            dim_company.dim_company_type,
            dim_company.dim_company_level,
            dim_company.dim_company_level_id,
            COUNT(
                DISTINCT (
                    IF (
                        dim_company.dim_company_status = "Active",
                        dim_company.dim_company_id,
                        NULL
                    )
                )
            ) AS companias_esperadas,
            COUNT(
                DISTINCT (
                    IF (
                        dim_company.dim_company_authorization_status = "Authorized",
                        dim_company.dim_company_id,
                        NULL
                    )
                )
            ) AS companias_registradas,
            count(distinct
                if (
                    dim_company.dim_company_status = "Active"
                    AND dim_company.dim_company_authorization_status <> "Authorized"
                    AND (
                        fact_companies_registration.owner_registered_indicator = "Registered" OR
                        fact_companies_registration.admin_registered_indicator = "Registered"
                    ),
                    dim_company.dim_company_id, null
                )
            ) as companias_in_process,
            COUNT(
                DISTINCT (
                    IF (
                        dim_profile.dim_profile_id = '.Profile::PRINCIPAL.'
                        AND fact_user_registration.authorized_indicator = "Authorized"
                        AND fact_user_registration.account_active_indicator = "Active",
                        dim_user.dim_user_id,
                        NULL
                    )
                )
            ) AS miembros_principal,
            COUNT(
                DISTINCT (
                    IF (
                        dim_profile.dim_profile_id = '.Profile::PARTNER_ADMIN.'
                        AND fact_user_registration.authorized_indicator = "Authorized"
                        AND fact_user_registration.account_active_indicator = "Active",
                        dim_user.dim_user_id,
                        NULL
                    )
                )
            ) AS miembros_admin,
            COUNT(
                DISTINCT (
                    IF (
                        dim_profile.dim_profile_id IN ('.Profile::SALES.')
                        AND fact_user_registration.authorized_indicator = "Authorized"
                        AND fact_user_registration.account_active_indicator = "Active",
                        dim_user.dim_user_id,
                        NULL
                    )
                )
            ) AS miembros_sales,
            COUNT(
                DISTINCT (
                    IF (
                        dim_profile.dim_profile_id IN (
                            '.Profile::PRINCIPAL.',
                            '.Profile::PARTNER_ADMIN.',
                            '.Profile::SALES.'
                        )
                        AND fact_user_registration.authorized_indicator = "Authorized"
                        AND fact_user_registration.account_active_indicator = "Active",
                        dim_user.dim_user_id,
                        NULL
                    )
                )
            ) AS miembros_total,
            COUNT(
               DISTINCT (
                IF (
                    dim_profile.dim_profile_id IN (
                          '.Profile::SALES.'
                    )
                    AND fact_user_registration.authorized_indicator <> "Authorized",
                    dim_user.dim_user_id,
                    NULL
                )
       )
              ) AS user_in_process,
            (
                COUNT(
                    DISTINCT (
                        IF (
                            dim_company.dim_company_authorization_status = "Authorized",
                            dim_company.dim_company_id,
                            NULL
                        )
                    )
                ) * 100 / COUNT(
                    DISTINCT (dim_company.dim_company_id)
                )
            ) AS porcentaje
        FROM
            dim_company
        INNER JOIN fact_user_registration ON dim_company.dim_company_sk = fact_user_registration.company_sk
        INNER JOIN dim_user ON dim_user.dim_user_sk = fact_user_registration.user_sk
        INNER JOIN dim_profile ON dim_profile.dim_profile_sk = fact_user_registration.profile_sk
        INNER JOIN fact_companies_registration ON fact_companies_registration.company_sk = dim_company.dim_company_sk
        WHERE
            dim_company.dim_company_demo = "Regular"
            AND dim_company.dim_company_approved = "Approved"
            AND dim_company.dim_company_status = "Active"';
            //-- AND dim_company.dim_company_region_id <> 0';

        if(!empty($region)){
            $query .= ' AND dim_company.dim_company_region_id = ' . $region;
        }
        if($groupBy != 'subregion' && !empty($companyType)){
            $query .= ' AND dim_company.dim_company_type_id = ' . $companyType;
        }
        if(!empty($subregion)){
            $query .= ' AND dim_company.dim_company_subregion_id = ' . $subregion;
        }

        if(is_null($groupBy)) {
            $query .= ' GROUP BY dim_company.dim_company_region_id, dim_company.dim_company_type_id';
        }elseif($groupBy == 'subregion') {
            $query .= ' GROUP BY dim_company.dim_company_region_id, dim_company.dim_company_type_id, dim_company.dim_company_subregion_id';
                }elseif($groupBy == 'company-level') {
            $query .= ' GROUP BY dim_company.dim_company_region_id, dim_company.dim_company_type_id, dim_company.dim_company_subregion_id, dim_company.dim_company_level_id';
        }

        $query .= ' ORDER BY             
                        dim_company.dim_company_region,
                        dim_company.dim_company_subregion,
                        dim_company.dim_company_type';

        //dump($query);die;

        $stmt = $this->dwConn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getRegistrationDashboardTotal($data)
    {
        $totals = [];

        foreach ($data as $regionData) {

            foreach ($regionData as $row) {

                foreach ($row as $col => $value) {

                    $totals[$col] = 0;
                }
            }
        }

        foreach ($data as $regionData) {

            foreach ($regionData as $row) {

                foreach ($row as $col => $value) {

                    $totals[$col] += $value;
                }
            }
        }

        // Patch for the totals
        $totals['porcentaje'] = ($totals['companias_registradas'] / $totals['companias_esperadas']) * 100;

        return $totals;
    }

    /**
     * Registered Performance Report Xls
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRegistrationPerformanceData()
    {
        $query = "
            SELECT
                dim_company.dim_company_region as region,
                dim_company.dim_company_subregion as sub_region,
                dim_company.dim_company_country as country,
                dim_company.dim_company_name as company,
                dim_company.dim_company_id_number as identification_number,
                dim_company.dim_company_tax_id as tax_number,
                dim_company.dim_company_subtype as company_sub_type,
                dim_company.dim_company_type as company_type,
                dim_company.dim_company_level as company_level,
                dim_company.dim_company_status as company_status,
                fact_companies_registration.company_creation_date_sk as company_invited_date,
                fact_companies_registration.company_authorized_indicator as company_authorized,
                dim_company.dim_company_registration_status as company_registered,
                dim_user.dim_user_person_full_name as user_name,
                dim_user.dim_user_email as email,
                dim_user.dim_user_work_phone as office,
                dim_user.dim_user_mobile_phone as mobile,
                dim_profile.dim_profile_name as profile,
                fact_user_registration.account_active_indicator as account_active,
                dim_user.dim_user_status as user_status,
                fact_user_registration.authorized_indicator as user_authorized,
                fact_user_registration.terms_actual_indicator as user_registered,
                fact_user_registration.registration_date_sk as registration_date,
                fact_user_registration.terms_actual_indicator as accepted_terms,
                fact_user_registration.approved_indicator as user_approved,
                fact_user_registration.approved_date_sk as approval_date,
                fact_user_registration.confirmed_indicator as user_confirmed,
                fact_user_registration.confirmation_date_sk as confirmed_date
            FROM
                dim_company
                INNER JOIN fact_user_registration ON dim_company.dim_company_sk = fact_user_registration.company_sk
                INNER JOIN dim_user ON dim_user.dim_user_sk = fact_user_registration.user_sk
                INNER JOIN dim_profile ON dim_profile.dim_profile_sk = fact_user_registration.profile_sk
                INNER JOIN fact_companies_registration ON fact_companies_registration.company_sk = dim_company.dim_company_sk
            WHERE
                dim_company.dim_company_demo = 'Regular'
            # AND dim_company.dim_company_region_id <> 0
            AND dim_company.dim_company_approved = 'Approved'
            AND dim_profile.dim_profile_id IN (
                            ".Profile::PRINCIPAL.",
                            ".Profile::PARTNER_ADMIN.",
                            ".Profile::SALES."
                        )
            GROUP BY
                dim_company.dim_company_id,
                dim_user.dim_user_id,
                dim_profile.dim_profile_id
            ORDER BY
                dim_company.dim_company_region_id,
                dim_company.dim_company_country_id,
                dim_company.dim_company_name
        ";

        $stmt = $this->dwConn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * Registered Performance Report Live Xls
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRegistrationPerformanceLiveData()
    {
        $query = "
            SELECT
                region.`name` AS company_region,
                country.`name` AS company_country,
                company.`name` AS company_name,
                company.identification_number,
                company.tax_number,
                company.created_at AS invited_at,
                IF( company.`status` = 1, 'Active',
                IF( company.`status` = 2, 'Pending for registration', 'Inactive')) AS company_status,
                IF( company.registered = 1, 'Registered', 'Not Registered' ) AS company_registered,
                company_type.`name` AS company_type,
                company_level.`name` AS company_level,
                person.firstname AS first_name,
                person.lastname AS last_name,
                #CONCAT(person.firstname, ' ', person.lastname) AS user_full_name,
                `user`.email,
                IF( `user`.enabled IS NULL, '', IF( `user`.enabled = 1, 'Active', 'Inactive')) AS user_status,
                IF( `user`.enabled IS NULL, '', IF( `user`.enabled = 1 AND `user`.registered = 1
                    AND company_user_profile.approved = 1 AND `user`.confirmed = 1, 'Yes', 'No')) AS user_authorized,
                IF( company_user_profile.approved IS NULL, '', IF( company_user_profile.approved = 1, 'Yes', 'No')) AS user_approved,
                company_user_profile.approve_date,
                IF( `user`.confirmed = 1, 'Yes', 'No') AS user_confirmed,
                `user`.confirmation_date,
                IF( `user`.registered = 1, 'Yes', 'No') AS user_registered,
                `user`.registration_date,
                IF( user_terms.approved = 1, 'Yes', 'No') AS user_terms,
                user_terms.created_at AS terms_date,
                `profile`.`name` AS profile_name,
                IF( `user`.enabled IS NULL, '', IF (company_user_profile.active = 1, 'Active', 'Inactive')) AS account_active_indicate
            FROM
                company
                LEFT JOIN country ON company.country_id = country.id
                LEFT JOIN company_level_relation ON company.id = company_level_relation.company_id
                LEFT JOIN company_level ON company_level_relation.company_level_id = company_level.id AND company_level_relation.active = 1
                LEFT JOIN company_subtype_relation ON company.id = company_subtype_relation.company_id
                LEFT JOIN company_subtype ON company_subtype_relation.company_subtype_id = company_subtype.id AND company_subtype_relation.active = 1
                LEFT JOIN company_type ON company_subtype.company_type_id = company_type.id
                LEFT JOIN sub_region_country ON sub_region_country.country_id = country.id
                LEFT JOIN region_subregion ON sub_region_country.subregion_id = region_subregion.sub_region_id
                LEFT JOIN region ON region_subregion.region_id = region.id
                LEFT JOIN company_user_profile ON company_user_profile.company_id = company.id
                LEFT JOIN `user` ON company_user_profile.user_id = `user`.id
                LEFT JOIN person ON `user`.person_id = person.id
                LEFT JOIN `profile` ON company_user_profile.profile_id = `profile`.id
                LEFT JOIN user_terms ON user_terms.user_id = `user`.id
            WHERE
                company.is_demo = 0
                AND company_type.visible = 1
            GROUP BY
                company.id,
                company_user_profile.user_id,
                company_user_profile.profile_id
            ORDER BY
                region.id,
                country.id,
                company.`name`,
                company.id
        ";

        $stmt = $this->defaultConn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    /**
     * Registered Performance Report Xls
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function registrationPotentials()
    {
        $headers = array(
            'dim_company_region' => 'Region',
            'dim_company_subregion' => 'Sub Region',
            'dim_company_country' => 'Country',
            'dim_company_name' => 'Company',
            'dim_company_id_number' => 'Identification Number',
            'dim_company_tax_id' => 'Tax Number',
            'dim_company_subtype' => 'Company Sub Type',
            'dim_company_type' => 'Company Type',
            'dim_company_level' => 'Company Level',
            'dim_company_status' => 'Company Status',
            'company_creation_date_sk' => 'Company Invited Date',
            'company_authorized_indicator' => 'Company Authorized',
            'user_full_name' => 'User Name',
            'dim_user_email' => 'Email',
            'dim_user_office' => 'Office',
            'dim_user_mobile' => 'Mobile',
            'dim_profile_name' => 'Profile',
            'dim_user_status' => 'User Status',
            'authorized_indicator' => 'User Authorized',
            'terms_actual_indicator' => 'User Registered',
            'registration_date_sk' => 'Registration Date',
            'terms_actual_indicator' => 'Accepted Terms',
            'approved_indicator' => 'User Approved',
            'approved_date_sk' => 'Approval Date',
            'confirmed_indicator' => 'User Confirmed',
            'confirmation_date_sk' => 'Confirmed Date',
        );

        $query = "
            SELECT
                dim_company.dim_company_region,
                dim_company.dim_company_subregion,
                dim_company.dim_company_country,
                dim_company.dim_company_name,
                dim_company.dim_company_id_number,
                dim_company.dim_company_tax_id,
                dim_company.dim_company_subtype,
                dim_company.dim_company_type,
                dim_company.dim_company_level,
                dim_company.dim_company_status,
                fact_companies_registration.company_creation_date_sk,
                fact_companies_registration.company_authorized_indicator,
                dim_user.dim_user_person_full_name AS user_full_name,
                -- dim_person.dim_person_document_number,
                dim_user.dim_user_email,
                '' AS dim_user_office,
                '' AS dim_user_mobile,
                dim_profile.dim_profile_name,
                '' AS dim_user_status,
                fact_user_registration.authorized_indicator,
                fact_user_registration.terms_actual_indicator,
                fact_user_registration.registration_date_sk,
                fact_user_registration.terms_actual_indicator,
                fact_user_registration.approved_indicator,
                fact_user_registration.approved_date_sk,
                fact_user_registration.confirmed_indicator,
                fact_user_registration.confirmation_date_sk
            FROM
                dim_user
            INNER JOIN fact_user_registration ON fact_user_registration.user_sk = dim_user.dim_user_sk
            INNER JOIN dim_company ON dim_company.dim_company_sk = fact_user_registration.company_sk
            INNER JOIN fact_companies_registration ON fact_companies_registration.company_sk = dim_company.dim_company_sk
            INNER JOIN dim_profile ON dim_profile.dim_profile_sk = fact_user_registration.profile_sk
            -- INNER JOIN dim_person ON dim_person.dim_person_sk = fact_user_registration.person_sk
            WHERE
                dim_company.dim_company_demo = 'Regular'
            AND dim_company.dim_company_approved = 'Approved'
            AND dim_company.dim_company_region_id <> 0
        ";

        $stmt = $this->dwConn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return array(
            'headers' => $headers,
            'data' => $result
        );
    }

    public function getRegistrationCompanies($region, $companyType = null, $companyLevel = null, $registered = null, $subregion = null)
    {
        $query = "
            SELECT
                dim_company.dim_company_region_id,
                dim_company.dim_company_region,
                dim_company.dim_company_country,
                dim_company.dim_company_id,
                dim_company.dim_company_name,
                dim_company.dim_company_type,
                dim_company.dim_company_level,
                IF(fact_companies_registration.owner_registered_indicator = 'Registered','YES','NO') AS principal_company,
                IF(fact_companies_registration.admin_registered_indicator = 'Registered','YES','NO') AS admin_company,
                COUNT(DISTINCT(dim_user.dim_user_id)) AS users_total,
                CONCAT(ROUND((IF(dim_company.dim_company_registration_status='Registered',1,0)
                     + IF(fact_companies_registration.owner_registered_indicator = 'Registered',1,0)
                     + IF(fact_companies_registration.admin_registered_indicator = 'Registered',1,0))/3*100),'%') AS porcentaje,
                dim_company.dim_company_status AS status_company
            FROM dim_company
            INNER JOIN fact_user_registration ON fact_user_registration.company_sk = dim_company.dim_company_sk
            INNER JOIN dim_user ON dim_user.dim_user_sk = fact_user_registration.user_sk
            INNER JOIN dim_profile ON dim_profile.dim_profile_sk = fact_user_registration.profile_sk
            INNER JOIN fact_companies_registration ON fact_companies_registration.company_sk = dim_company.dim_company_sk
            WHERE dim_company.dim_company_demo = 'Regular'
                AND dim_company.dim_company_approved = 'Approved'
                AND dim_company.dim_company_status = 'Active'
                AND dim_company.dim_company_region_id = ". $region;

            if ($companyType) {
                $query .= " AND dim_company.dim_company_type_id = " . $companyType;
            }

            if ($companyLevel){
                $query .= " AND dim_company.dim_company_level_id = " . $companyLevel;
            }

            if ($subregion){
                $query .= " AND dim_company.dim_company_subregion_id = " . $subregion;
            }

            if ($registered == 1) {
                $query .= " AND dim_company.dim_company_authorization_status = 'Authorized' ";
            } elseif ($registered == 0 && $registered != null) {
                $query .= " AND dim_company.dim_company_authorization_status <> 'Authorized' ";
                $query .= "
                    AND
                    (
			fact_companies_registration.owner_registered_indicator = 'Registered' OR
                        fact_companies_registration.admin_registered_indicator = 'Registered'
                    )
                ";
            }

        $query .= " GROUP BY dim_company.dim_company_id";

        $stmt = $this->dwConn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }

    public function getRegistrationUsers($region, $companyType, $profile = null, $companyLevel = null, $subregion = null)
    {
        $query = "
            SELECT
                dim_company.dim_company_region,
                dim_company.dim_company_country_id,
                dim_company.dim_company_country,
                dim_company.dim_company_id,
                dim_company.dim_company_name,
                dim_company.dim_company_type,
                dim_company.dim_company_level,
                SUBSTRING_INDEX(SUBSTRING_INDEX(dim_user.dim_user_person_full_name, ' ', 1), ' ', -1) as user_first_name,
                SUBSTRING_INDEX(SUBSTRING_INDEX(dim_user.dim_user_person_full_name, ' ', 2), ' ', -1) as user_last_name,
                dim_profile.dim_profile_id,
                dim_profile.dim_profile_name,
                IF(fact_user_registration.terms_actual_indicator = 'Approved','YES','NO') AS terms_user,
                IF(fact_user_registration.confirmed_indicator = 'Confirmed','YES','NO') AS confirmed_user,
                IF(fact_user_registration.approved_indicator = 'Approved','YES','NO') AS approved_user,
                CONCAT(ROUND((IF(fact_user_registration.terms_actual_indicator = 'Approved',1,0)
                     + IF(fact_user_registration.confirmed_indicator = 'Confirmed',1,0)
                     + IF(fact_user_registration.approved_indicator = 'Approved',1,0))/3*100),'%') AS porcentaje
            FROM dim_user
            INNER JOIN fact_user_registration ON fact_user_registration.user_sk = dim_user.dim_user_sk
            INNER JOIN dim_company ON dim_company.dim_company_sk = fact_user_registration.company_sk
            INNER JOIN dim_profile ON dim_profile.dim_profile_sk = fact_user_registration.profile_sk
            WHERE dim_company.dim_company_demo = 'Regular'
                AND dim_company.dim_company_approved = 'Approved'
                AND dim_company.dim_company_status = 'Active  '
                AND dim_company.dim_company_region_id = ". $region ;

            if($profile == null){
                $query .= " AND dim_profile.dim_profile_id in (
                            ".Profile::SALES."
                        ) AND fact_user_registration.authorized_indicator <> 'Authorized'";
            }else{
                $query .=" AND dim_profile.dim_profile_id = ". $profile ."
                AND fact_user_registration.authorized_indicator = 'Authorized'
                AND fact_user_registration.account_active_indicator = 'Active'";
            }
            if($companyType){
                $query .= " AND dim_company.dim_company_type_id = ". $companyType;
            }
            if($companyLevel){
                $query .= " AND dim_company.dim_company_level_id = " .$companyLevel;
            }

        if($subregion){
            $query .= " AND dim_company.dim_company_subregion_id = " .$subregion;
        }

            $query .= " GROUP BY dim_user.dim_user_id 
            order by dim_company.dim_company_region, 
            dim_company.dim_company_country, 
            dim_company.dim_company_name;";

        $stmt = $this->dwConn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        //dump($query);die;

        return $result;
    }

    public function getCompanyUsersDetail($company)
    {
        $query = "
            SELECT
                dim_user.dim_user_person_full_name AS dim_person_full_name,
                dim_user.dim_user_email,
                IF(fact_user_registration.terms_actual_indicator = 'Approved','YES','NO') AS terms_user,
                IF(fact_user_registration.confirmed_indicator = 'Confirmed','YES','NO') AS confirmed_user,
                IF(fact_user_registration.approved_indicator = 'Approved','YES','NO') AS approved_user,
                dim_profile.dim_profile_name
            FROM dim_user
            INNER JOIN fact_user_registration ON fact_user_registration.user_sk = dim_user.dim_user_sk
            INNER JOIN dim_company ON dim_company.dim_company_sk = fact_user_registration.company_sk
            INNER JOIN dim_profile ON dim_profile.dim_profile_sk = fact_user_registration.profile_sk
            -- INNER JOIN dim_person ON dim_person.dim_person_sk = fact_user_registration.person_sk
            WHERE
                dim_company.dim_company_demo = 'Regular'
                AND dim_company.dim_company_approved = 'Approved'
                AND dim_company.dim_company_id = ". $company;

        $stmt = $this->dwConn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

        return $result;
    }
}
