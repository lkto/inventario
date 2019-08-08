<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 7/08/2019
 * Time: 1:20 PM
 */

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/home", name="admin_home")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index()
    {
        return  $this->render('admin/home.html.twig', [
            'message' => 'error.not_bulletin'
        ]);
    }
}