<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 8/11/2019
 * Time: 10:49 AM
 */

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class FrondController extends Controller
{
    /**
     * @Route("/", name="microsite")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function clientList()
    {
        return  $this->render('frond/home.html.twig');
    }

}