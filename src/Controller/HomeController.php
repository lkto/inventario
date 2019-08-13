<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 10/08/2019
 * Time: 8:34 AM
 */

namespace App\Controller;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class HomeController extends Controller
{
    /**
     * @Route("/home", name="admin_home")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index()
    {
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        $productDisable = $productRepository->getProductDisable();
        $productNoStock = $productRepository->getProductsNotStock();
        return  $this->render('admin/home/home.html.twig', [
            'productDisable' => $productDisable,
            'productNoStock' => $productNoStock
        ]);
    }

    /**
     * @Route("/home/pdf", name="admin_home_pdf")
     * @throws \Exception
     */
    public function pdf()
    {
        $pdfGenerator = $this->get('spraed.pdf.generator');
        $html = $this->renderView('admin/home/home.html.twig');
        return new Response($pdfGenerator->generatePDF($html),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="out.pdf"'
            )
        );
    }

}