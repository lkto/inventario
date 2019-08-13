<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 10/08/2019
 * Time: 8:38 AM
 */

namespace App\Controller;
use App\Entity\Client;
use App\Form\Type\ClientType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/people/client")
 */
class ClientController extends Controller
{
    /**
     * @Route("/list", name="admin_client_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function clientList()
    {
        $client = $this->getDoctrine()->getRepository(Client::class)->findAll();
        return  $this->render('admin/people/client/client_list.html.twig', [
            'client' => $client
        ]);
    }

    /**
     * @Route("/add", name="admin_client_add")
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

        return $this->render('admin/people/client/client_add.html.twig', [
            'client' => $client,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/state/{id}", name="admin_client_state")
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
     * @Route("/edit/{id}", name="admin_client_edit")
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

        return $this->render('admin/people/client/client_edit.html.twig', [
            'client' => $client,
            'form' => $form->createView()
        ]);
    }

}