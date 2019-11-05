<?php

namespace App\Report\Redemption;

use App\Report\ExcelExport;
use App\Report\ExportReportInterface;
use App\Report\Http\HttpResponseTrait;
use App\Report\NotFoundException;

/**
 * @author Manuel Aguirre <programador.manuel@gmail.com>
 */
class RedemptionPerformanceLiveReport implements ExportReportInterface
{
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
        return 'Registration Performance Live';
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->repository->getRegistrationPerformanceLiveData();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return array(
            'company_region' => 'Region',
            'company_country' => 'Country',
            'company_name' => 'Company',
            'identification_number' => 'Identification Number',
            'tax_number' => 'Tax Number',
            'company_type' => 'Company Type',
            'company_level' => 'Company Level',
            'company_status' => 'Company Status',
            'invited_at' => 'Company Invited Date',
            'company_registered' => 'Company Regitered',
            'first_name' => 'User First Name',
            'last_name' => 'User Last Name',
            'email' => 'Email',
            'profile_name' => 'Profile',
            'account_active_indicate' => 'Account Active',
            'user_status' => 'User Status',
            'user_authorized' => 'User Authorized',
            'user_registered' => 'User Registered',
            'registration_date' => 'Registration Date',
            'user_terms' => 'Accepted Terms',
            'user_approved' => 'User Approved',
            'approve_date' => 'Approval Date',
            'user_confirmed' => 'User Confirmed',
            'confirmation_date' => 'Confirmed Date'
        );
    }
}