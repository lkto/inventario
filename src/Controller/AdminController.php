<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 7/08/2019
 * Time: 1:20 PM
 */

namespace App\Controller;
use App\Entity\Client;
use App\Entity\User;
use App\Form\Type\ClientType;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/adminU")
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
        return  $this->render('admin/home.html.twig');
    }

    /**
     * @Route("/user/list", name="admin_user_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function userList()
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findAll();
        return  $this->render('admin/user/user_list.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/user/state/{id}", name="admin_user_state")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function userState($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $id]);
        $user->setEnabled(!$user->isEnabled());
        $em->persist($user);
        $em->flush();
        return  $this->redirectToRoute('admin_user_list');
    }

    /**
     * @Route("/client/list", name="admin_client_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function clientList()
    {
        $client = $this->getDoctrine()->getRepository(Client::class)->findAll();
        return  $this->render('admin/user/client_list.html.twig', [
            'client' => $client
        ]);
    }

    /**
     * @Route("/client/add", name="admin_client_add")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function clientAdd(Request $request )
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($client);
            $em->flush();
            $this->addFlash(
                'notice',
                'Cliente registrado con exito'
            );
            return $this->redirectToRoute('admin_client_list');
        }

        return $this->render('admin/register/client.html.twig', [
            'client' => $client,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/client/state/{id}", name="admin_client_state")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function clientState($id)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['id' => $id]);
        $client->setEnabled(!$client->isEnabled());
        $em->persist($client);
        $em->flush();
        return  $this->redirectToRoute('admin_client_list');
    }

    /**
     * @Route("/client/edit/{id}", name="admin_client_edit")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function clientEdit(Request $request, $id )
    {
        $client = $this->getDoctrine()->getRepository(Client::class)->findOneBy(['id' => $id]);
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($client);
            $em->flush();
            $this->addFlash(
                'notice',
                'Cliente modificado con exito'
            );
            return $this->redirectToRoute('admin_client_list');
        }

        return $this->render('admin/edit/client.html.twig', [
            'client' => $client,
            'form' => $form->createView()
        ]);
    }
}