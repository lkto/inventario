<?php
/**
 * Created by PhpStorm.
 * User: Alberto Patiño
 * Date: 20-08-2015
 * Time: 01:26 PM
 */

namespace App\Report\Registration;


use App\Report\ExcelExport;
use App\Report\ExportReportInterface;
use App\Report\Http\HttpResponseTrait;
use App\Report\NotFoundException;

class RegistrationPerformanceReport implements ExportReportInterface
{

    use HttpResponseTrait;

    /**
     * @var ExcelExport
     */
    private $export;

    /**
     * @var RegistrationRepository
     */
    private $repository;

    /**
     * @param ExcelExport $excelExport
     * @param RegistrationRepository $repository
     */
    public function __construct(ExcelExport $excelExport, RegistrationRepository $repository)
    {
        $this->export = $excelExport;
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Registration Performance';
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->repository->getRegistrationPerformanceData();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return array(
            'region' => 'Region',
            'sub_region' => 'Sub Region',
            'country' => 'Country',
            'company' => 'Company',
            'identification_number' => 'Identification Number',
            'tax_number' => 'Tax Number',
            'company_sub_type' => 'Company Sub Type',
            'company_type' => 'Company Type',
            'company_level' => 'Company Level',
            'company_status' => 'Company Status',
            'company_invited_date' => 'Company Invited Date',
            'company_authorized' => 'Company Authorized',
            'company_registered' =>'Company Regitered',
            'user_name' => 'User Name',
            'email' => 'Email',
            'office' => 'Office',
            'mobile' => 'Mobile',
            'profile' => 'Profile',
            'account_active' => 'Account Active',
            'user_status' => 'User Status',
            'user_authorized' => 'User Authorized',
            'user_registered' => 'User Registered',
            'registration_date' => 'Registration Date',
            'accepted_terms' => 'Accepted Terms',
            'user_approved' => 'User Approved',
            'approval_date' => 'Approval Date',
            'user_confirmed' => 'User Confirmed',
            'confirmed_date' => 'Confirmed Date',
        );
    }
}