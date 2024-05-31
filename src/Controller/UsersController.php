<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Users;
use App\Form\UsersFormType;

class UsersController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/users', name: 'users_inicio')]
    public function index(): Response
    {
        $users = $this->em->getRepository(Users::class)->findBy(array(), array('id' => 'desc'));

        $usuarios = [];
        foreach ($users as $user) {
            $usuarioArray = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'password' => $user->getPassword(),
                'active' => $user->getActive(),
                'fecha_creacion' => $user->getCreatedAt()->format('Y-m-d H:i:s')
            ];
            $usuarios[] = $usuarioArray;
        }

        return $this->render('users/users.html.twig', ['users' => $usuarios, 'errors' => array()]);
    }

    #[Route('/users/add', name: 'add_users')]
    public function users_add(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder): Response
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
                    return $this->render('users/add_user.html.twig', ['form' => $form->createView(), 'errors' => $errors]);
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
                    $entity->setActive($campos->getActive());
                    $entity->setCreatedAt(new \DateTime());
                    $entity->setRoles(['ROLE_ADMIN']);
                    $this->em->persist($entity);
                    $this->em->flush();

                    $this->addFlash('css', 'success');
                    $this->addFlash('mensaje', 'Se creó el usuario correctamente');
                    return $this->redirectToRoute('users_inicio');
                }
            } else
            {
                $this->addFlash('css', 'warning');
                $this->addFlash('mensaje', 'Ocurrio un error inesperado');
                return $this->redirectToRoute('users_add');
            }
        }

        return $this->render('users/add_user.html.twig', ['form' => $form->createView(), 'errors' => array()]);
    }

    #[Route('/users/edit/{id}', name: 'edit_users')]
    public function users_edit(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder, int $id): Response
    {
        $entity = $this->em->getRepository(Users::class)->find($id);

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
                    return $this->render('users/edit_user.html.twig', ['form' => $form->createView(), 'errors' => $errors, 'entity' => $entity]);
                } else
                {
                    $campos = $form->getData();
                    $entity->setUsername($campos->getUsername());
                    // La contraseña en texto plano
                    $password = $campos->getPassword();
                    // Codificamos la contraseña
                    $encodedPassword = $passwordEncoder->encodePassword($entity, $password);
                    // Seteamos la password encriptada
                    $entity->setPassword($encodedPassword);
                    $entity->setActive($campos->getActive());
                    $entity->setCreatedAt(new \DateTime());
                    $entity->setRoles(['ROLE_ADMIN']);
                    $this->em->flush();

                    return $this->redirectToRoute('users_inicio');
                }
            } else
            {
                $this->addFlash('css', 'warning');
                $this->addFlash('mensaje', 'Ocurrio un error inesperado');
                return $this->redirectToRoute('users_add');
            }
        }

        return $this->render('users/edit_user.html.twig', ['form' => $form->createView(), 'errors' => array(), 'entity' => $entity]);
    }

    #[Route('/users/delete/{id}', name: 'delete_users')]
    public function users_delete(Request $request, int $id)
    {
        $entity = $this->em->getRepository(Users::class)->find($id);

        $this->em->remove($entity);
        $this->em->flush();

        $this->addFlash('css', 'success');
        $this->addFlash('mensaje', 'Se eliminó el registro correctamente');
        return $this->redirectToRoute('users_inicio');
    }
}
