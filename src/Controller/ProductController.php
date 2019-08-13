<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 12/08/2019
 * Time: 11:09 AM
 */

namespace App\Controller;
use App\Entity\Product;
use App\Form\Type\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/product")
 */
class ProductController extends Controller
{
    /**
     * @Route("/list", name="admin_product_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function productList()
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findAll();
        return  $this->render('admin/product/product_list.html.twig', [
            'product' => $product
        ]);
    }

    /**
     * @Route("/add", name="admin_product_add")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function productAdd(Request $request )
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $productExist = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['code' => $product->getCode()]);
            if(!$productExist)
            {
                $em = $this->getDoctrine()->getManager();
                $em->persist($product);
                $em->flush();
                $this->addFlash(
                    'notice',
                    'Producto registrado con exito'
                );
                return $this->redirectToRoute('admin_product_list');
            }

            $this->addFlash(
                'error',
                'Codigo de producto existente, por favor intente con uno diferente'
            );

        }

        return $this->render('admin/product/product_add.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="admin_product_edit")
     * @throws \Exception
     */
    public function productEdit(Request $request, $id )
    {
        $product = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['id' => $id]);
        $actualCode = $product->getCode();
        if(!$product){
            return new \Exception('Producto no existe');
        }
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categoryExist = $this->getDoctrine()->getRepository(Product::class)->findOneBy(['code' => $product->getCode()]);
            if(!$categoryExist || $actualCode === $product->getCode() ){
                $em = $this->getDoctrine()->getManager();
                $em->persist($product);
                $em->flush();
                $this->addFlash(
                    'notice',
                    'Producto editada con exito'
                );
                return $this->redirectToRoute('admin_product_list');
            }

            $this->addFlash(
                'error',
                'Codigo de producto existente, por favor intente con uno diferente'
            );

        }

        return $this->render('admin/product/product_edit.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }


}