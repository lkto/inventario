<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 10/08/2019
 * Time: 8:48 AM
 */

namespace App\Controller;
use App\Entity\Category;
use App\Form\Type\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/product/category")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/list", name="admin_category_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function categoryList()
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findAll();
        return  $this->render('admin/product/category/category_list.html.twig', [
            'category' => $category
        ]);
    }

    /**
     * @Route("/add", name="admin_category_add")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function categoryAdd(Request $request )
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categoryExist = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['code' => $category->getCode()]);
            if(!$categoryExist){
                $em = $this->getDoctrine()->getManager();
                $em->persist($category);
                $em->flush();
                $this->addFlash(
                    'notice',
                    'Categoria registrada con exito'
                );
                return $this->redirectToRoute('admin_category_list');
            }

            $this->addFlash(
                'error',
                'Codigo de categoria existente, por favor intente con uyno diferente'
            );

        }

        return $this->render('admin/product/category/category_add.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}", name="admin_category_edit")
     * @throws \Exception
     */
    public function categoryEdit(Request $request, $id )
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['id' => $id]);
        $actualCode = $category->getCode();
        if(!$category){
            return new \Exception('Categoria no existe');
        }
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categoryExist = $this->getDoctrine()->getRepository(Category::class)->findOneBy(['code' => $category->getCode()]);
            if(!$categoryExist || $actualCode === $category->getCode() ){
                $em = $this->getDoctrine()->getManager();
                $em->persist($category);
                $em->flush();
                $this->addFlash(
                    'notice',
                    'Categoria editada con exito'
                );
                return $this->redirectToRoute('admin_category_list');
            }

            $this->addFlash(
                'error',
                'Codigo de categoria existente, por favor intente con uyno diferente'
            );

        }

        return $this->render('admin/product/category/category_edit.html.twig', [
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

}