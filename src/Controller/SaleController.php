<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 12/08/2019
 * Time: 12:06 PM
 */

namespace App\Controller;
use App\Entity\Client;
use App\Entity\Product;
use App\Entity\SaleDetails;
use App\Entity\Sales;
use App\Report\ExcelExport;
use App\Services\PrintInvoice;
use http\Client\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/sales")
 */
class SaleController extends Controller
{
    /**
     * @Route("/list", name="admin_sale_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function saleList()
    {
        $sale = $this->getDoctrine()->getRepository(Sales::class)->findAll();

        return  $this->render('admin/sales/sale_list.html.twig', [
            'sale' => $sale
        ]);
    }

    /**
     * @Route("/add", name="admin_sale_add")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function saleAdd()
    {
        $client = $this->getDoctrine()->getRepository(Client::class)->findBy(['enabled' => true]);
        $product = $this->getDoctrine()->getRepository(Product::class)->getEnableProduct();
        return  $this->render('admin/sales/sale_add.html.twig', [
            'client' => $client,
            'product' => $product
        ]);
    }

    /**
 * @Route("/getClients", name="admin_sale_get_clients")
 */
    public function getClients(Request $request)
    {
        $identify = $request->query->get('identify');
        $client = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['identify' => $identify]);
        $data = [
            "name" => $client->getFirstName() . " " . $client->getLastName(),
            "identify" => $client->getIdentify()
        ];

        $array = new \Symfony\Component\HttpFoundation\Response(json_encode( $data) );
        $array->headers->set('Content-Type', 'application/json');

        return $array;

    }

    /**
     * @Route("/getProducts", name="admin_sale_get_products")
     */
    public function getProducts(Request $request)
    {
        $identify = $request->query->get('identify');
        $products= $this->getDoctrine()->getRepository(Product::class)->findOneBy(['code' => trim($identify)]);

        $data = [
            "value" => $products->getValue(),
            "stock" => $products->getStock()
        ];

        $array = new \Symfony\Component\HttpFoundation\Response(json_encode( $data) );
        $array->headers->set('Content-Type', 'application/json');

        return $array;

    }

    /**
     * @Route("/saveSale", name="admin_sale_save")
     */
    public function saveSale(Request $request)
    {
        $data = $request->query->get('data');
        $em = $this->getDoctrine()->getManager();
        if($data[0]['user']){
            $client = $data[0]['user'];
            $client = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['identify' => $client]);
            $code = $this->generateCode();
            $sale = new Sales();
            $sale->setClient($client);
            $sale->setUser($this->getUser());
            $sale->setCode($code);
            $em->persist($sale);
            $em->flush();

            foreach ($data[1]['detail'] as $detail){
                $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['code' => trim($detail['product'])]);
                $saleDetail = new SaleDetails();
                $saleDetail->setProduct($product);
                $saleDetail->setSale($sale);
                $saleDetail->setCount($detail['count']);
                $saleDetail->setValueUnit($detail['valueUnit']);
                $saleDetail->setValueTotal($detail['total']);

                $product->setStock($product->getStock() - $detail['count']);
                $em->persist($product);
                $em->persist($saleDetail);
            }

            $em->flush();

            $saleInfo = [
                'id' => $sale->getId()
            ];



        }else {
            $saleInfo = [];
        }

        $array = new \Symfony\Component\HttpFoundation\Response(json_encode( $saleInfo) );
        $array->headers->set('Content-Type', 'application/json');

        return $array;
    }

    /**
     * @Route("/editSale/{id}", name="admin_sale_edit")
     */
    public function editSale(Request $request, $id)
    {
        $client = $this->getDoctrine()->getRepository(Client::class)->findBy(['enabled' => true]);
        $product = $this->getDoctrine()->getRepository(Product::class)->getEnableProduct();
        $sale = $this->getDoctrine()->getRepository(Sales::class)->findOneBy(['id' => $id]);
        return  $this->render('admin/sales/sale_edit.html.twig', [
            'client' => $client,
            'product' => $product,
            "sale" => $sale
        ]);
    }

    /**
     * @Route("/saveEditSale", name="admin_edit_sale_save")
     */
    public function saveEditSale(Request $request)
    {
        $data = $request->query->get('data');
        $em = $this->getDoctrine()->getManager();
        if($data[0]['user']){
            $client = $data[0]['user'];
            $client = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['identify' => $client]);
            $code = $this->generateCode();
            $sale = $this->getDoctrine()->getRepository(Sales::class)->findOneBy(['id' => $data[2]['sale']]);

            foreach ( $sale->getSaleDetails() as $detail){
                $sale->removeSaleDetails($detail);
            }
            $em->persist($sale);
            $em->flush();
            foreach ($data[1]['detail'] as $detail){
                $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['code' => trim($detail['product'])]);
                $saleDetail = new SaleDetails();
                $saleDetail->setProduct($product);
                $saleDetail->setSale($sale);
                $saleDetail->setCount($detail['count']);
                $saleDetail->setValueUnit($detail['valueUnit']);
                $saleDetail->setValueTotal($detail['total']);

                $product->setStock($product->getStock() - $detail['count']);
                $em->persist($product);
                $em->persist($saleDetail);
            }

            $em->flush();

            $saleInfo = [
                'id' => $sale->getId()
            ];



        }else {
            $saleInfo = [];
        }

        $array = new \Symfony\Component\HttpFoundation\Response(json_encode( $saleInfo) );
        $array->headers->set('Content-Type', 'application/json');

        return $array;
    }

    /**
     * @Route("/detail/{id}", name="admin_sale_detail")
     */
    public function saleDetail(Request $request, $id)
    {
        $sale = $this->getDoctrine()->getRepository(Sales::class)->findOneBy(['id' => $id]);
        return $this->render('admin/sales/sale_detail.html.twig', ['sale' => $sale]);
    }

    /**
     * @Route("/print/{id}", name="admin_sale_print")
     */
    public function salePrint(Request $request, $id, PrintInvoice $invoice, ExcelExport $excelExport)
    {
        $sale = $this->getDoctrine()->getRepository(Sales::class)->findOneBy(['id' => $id]);
        $generateExcel = $invoice->generateExcel($sale);
        $title = "Venta numero ". $sale->getCode();
        $writer = $excelExport->export($generateExcel['data'],$generateExcel['key'], $title );

        return $excelExport->createResponseFromWriter($writer, $title);
    }

    public function generateCode(){
        $date = new \DateTime('now');
        $id = 1;
        $ultSale = $this->getDoctrine()->getRepository(Sales::class)->getFinallyRegister();
        if($ultSale) {
            $id = $ultSale[0]->getId();
        }
        $code = (1000 + ($id + 1)). $date->format('Y');

        return $code;


    }
}