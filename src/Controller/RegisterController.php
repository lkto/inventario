<?php
/**
 * Created by PhpStorm.
 * User: EBEJARANO
 * Date: 9/08/2019
 * Time: 1:56 PM
 */

namespace App\Controller;
use App\Entity\User;
use App\Form\Type\RegisterUserType;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Annotation\Route;



class RegisterController extends Controller
{
    /**
     * @Route("/admin/register/")
     */
    public function return404ForRegisterRoute()
    {
        throw $this->createNotFoundException();
    }

    /**
     * @Route(
     *     "/admin/register/user/",
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

        return $this->render('admin/register/user.html.twig', [
            "user" => $user,
            "form" => $form->createView(),
        ]);
    }

}