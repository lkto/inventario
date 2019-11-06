<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 6/11/2019
 * Time: 8:36 AM
 */

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Sales;
use App\Report\ExcelExport;
use App\Services\ProductReport;
use App\Services\SaleForClientReport;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/report")
 */
class ReportController extends Controller
{
    /**
     * @Route("/sale", name="admin_sale_report")
     * @throws \Exception
     */
    public function SaleForClient(ExcelExport $excelExport, SaleForClientReport $saleForClientReport)
    {
        $sale = $this->getDoctrine()->getRepository(Sales::class)->findAll();
        $generateExcel = $saleForClientReport->generateExcel($sale);
        $title = "Reporte de ventas ";
        $writer = $excelExport->export($generateExcel['data'],$generateExcel['key'], $title );

        return $excelExport->createResponseFromWriter($writer, $title);
    }

    /**
     * @Route("/product", name="admin_product_report")
     * @throws \Exception
     */
    public function product(ExcelExport $excelExport, ProductReport $productReport)
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findAll();
        $generateExcel = $productReport->generateExcel($product);

        $title = "Reporte de productos ";
        $writer = $excelExport->export($generateExcel['data'],$generateExcel['key'], $title );

        return $excelExport->createResponseFromWriter($writer, $title);
    }

}