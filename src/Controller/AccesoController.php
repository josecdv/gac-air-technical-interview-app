<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\LoginFormType;
use App\Form\UsersFormType;
use App\Entity\Users;

class AccesoController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'acceso_login')]
    public function login(Request $request, ValidatorInterface $validator): Response
    {
        $entity = new Users();

        $form = $this->createForm(LoginFormType::class, $entity);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted())
        {
            if ($this->isCsrfTokenValid('generico', $submittedToken))
            {
                $errors = $validator->validate($entity);
                if (count($errors) > 0)
                {
                    return $this->render('acceso/index.html.twig', ['form' => $form->createView(), 'errors' => $errors]);
                } else
                {
                    $campos = $form->getData();
                    $user = $this->em->getRepository(Users::class)->findOneBy(['username' => $campos->getUsername()]);
                    if (!$user)
                    {
                        $this->addFlash('css', 'danger');
                        $this->addFlash('mensaje', 'Las credenciales ingresadas no son correctas');
                        return $this->redirectToRoute('acceso_login');
                    }
                    
                    return $this->redirectToRoute('products_inicio');
                }
            } else
            {
                $this->addFlash('css', 'warning');
                $this->addFlash('mensaje', 'Ocurrio un error inesperado');
                return $this->redirectToRoute('acceso_login');
            }
        }

        return $this->render('acceso/index.html.twig', ['form' => $form->createView(), 'errors' => array()]);
    }

    #[Route('/registro', name: 'acceso_registro')]
    public function registro(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $entity = new Users();

        $form = $this->createForm(UsersFormType::class, $entity);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted())
        {
            if ($this->isCsrfTokenValid('generico', $submittedToken))
            {
                $errors = $validator->validate($entity);
                if (count($errors) > 0)
                {
                    return $this->render('acceso/sign-up.html.twig', ['form' => $form->createView(), 'errors' => $errors]);
                } else 
                {
                    $campos = $form->getData();
                    $existe = $this->em->getRepository(Users::class)->findOneBy(['username' => $campos->getUsername()]);
                    if ($existe)
                    {
                        $this->addFlash('css', 'danger');
                        $this->addFlash('mensaje', 'El Username {$campos->getUsername()} ya esta siendo usado por otro usuario');
                        return $this->redirectToRoute('acceso_registro');
                    }
                    $entity->setUsername($campos->getUsername());
                    // La contraseña en texto plano
                    $password = $campos->getPassword();
                    // Codificamos la contraseña
                    $encodedPassword = $passwordEncoder->encodePassword($entity, $password);
                    // Seteamos la password encriptada
                    $entity->setPassword($encodedPassword);
                    $entity->setActive(1);
                    $entity->setCreatedAt(new \DateTime());
                    $entity->setRoles(['ROLE_ADMIN']);
                    $this->em->persist($entity);
                    $this->em->flush();

                    return $this->redirectToRoute('acceso_success');
                }
            } else
            {
                $this->addFlash('css', 'warning');
                $this->addFlash('mensaje', 'Ocurrio un error inesperado');
                return $this->redirectToRoute('acceso_registro');
            }
        }

        return $this->render('acceso/sign-up.html.twig', ['form' => $form->createView(), 'errors' => array()]);
    }

    #[Route('/success', name: 'acceso_success')]
    public function acceso_success()
    {
        return $this->render('acceso/success.html.twig');
    }

    #[Route('/logout', name: 'acceso_logout')]
    public function acceso_logout()
    {

    }
}
