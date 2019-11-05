<?php
/**
 * User: Cesar Lizarraga
 * Date: 2017-07-29
 * Time: 12:03 PM
 */

namespace App\Report\Redemption;


use App\Report\ExcelExport;
use App\Report\ExportReportInterface;
use App\Report\Http\HttpResponseTrait;
use App\Report\NotFoundException;

class RedemptionPerformanceReport implements ExportReportInterface
{

    use HttpResponseTrait;

    /**
     * @var ExcelExport
     */
    private $export;

    /**
     * @var RedemtionRepository
     */
    private $repository;

    /**
     * @param ExcelExport $excelExport
     * @param RedemptionRepository $repository
     */
    public function __construct(ExcelExport $excelExport, RedemptionRepository $repository)
    {
        $this->export = $excelExport;
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'Redemption Report';
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->repository->getRedemptionPerformanceLiveData();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return array(
                'company_region' => 'Company Region',
                'company_subregion' => 'Company SubRegion',
                'company_name' => 'Company Name',
                'company_type' => 'Company Type',
                'company_level' => 'Company Level',
                'user_first_name' => 'User First Name',
                'user_last_name' => 'User Last Name',
                'user_profile' => 'User Profile',
                'user_document_identity' => 'User Document Identity',
                'user_status' => 'User Status',
                'user_birthday' => 'User Birthday',
                'user_email' => 'User Email',
                'purchase_order_number' => 'Purchase Control Number',
                'purchase_prize' => 'Purchase Prize',
                'purchase_status' => 'Purchase Status',
                'purchase_address' => 'Purchase Address',
                'purchase_state' => 'Purchase State',
                'purchase_city' => 'Purchase City',
                'purchase_postal_code' => 'Purchase Postal Code',
                'points_redeemed' => 'Purchase Total Points',
                'purchase_created_date' => 'Purchase Requested Date',
                'purchase_reject_date' => 'Purchase Rejected Date',
                'purchase_pre_approved_date' => 'Purchase Preapproved Date',
                'purchase_shipped_date' => 'Purchase Shipped Date',
                'purchase_delivered_date' => 'Purchase Delivered Date',
                'purchase_turnaround' => 'Purchase Turnaround',
                'purchase_comment' => 'Purchase Comment',
                'purchase_comment_requested' => 'Purchase Comment Requested',
                'purchase_comment_delivered' => 'Purchase Comment Delivered'
        );
    }
}