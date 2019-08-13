<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 10/08/2019
 * Time: 8:42 AM
 */

namespace App\Controller;
use App\Entity\User;
use App\Form\Type\RegisterUserType;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/people/user")
 */
class UserController extends Controller
{
    /**
     * @Route(
     *     "/add",
     *     name="register_user",
     *     )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws
     */
    public function registerAction(Request $request )
    {
        $user = new User();
        $dispatcher = $this->get('event_dispatcher');
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
        $form = $this->createForm(RegisterUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() ) {
            if($form->isValid()){

                $user->setUsername($user->getEmail());
                $user->setUsernameCanonical($user->getEmail());
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->container->get('logger')->info(
                    sprintf('New user registration: %s', $user)
                );

                $this->addFlash(
                    'notice',
                    'Usuario registrado con exito'
                );

                return $this->redirectToRoute('admin_user_list');
            }
        }

        return $this->render('admin/people/user/user_add.html.twig', [
            "user" => $user,
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("/list", name="admin_user_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function userList()
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findAll();
        return  $this->render('admin/people/user/user_list.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/state/{id}", name="admin_user_state")
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

}