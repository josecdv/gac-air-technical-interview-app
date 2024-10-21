<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\ChangePasswordType;
use App\Form\UsersType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/users')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'app_users_index', methods: ['GET'])]
    public function index(UsersRepository $usersRepository): Response
    {
        return $this->render('users/index.html.twig', [
            'users' => $usersRepository->findAll(), 'tableArgs' => [
                "name" => "Usuarios",
                "btnText" => " Nuevo usuario",
                "btnRoute" => "app_users_new",
            ]
        ]);
    }

    #[Route('/new', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UsersRepository $usersRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new Users();
        dump($request);
        $form = $this->createForm(UsersType::class, $user);
        $form->remove('roles');
        $form->remove('createdAt');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $usersRepository->add($user);
            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }
        $form->remove('roles');
        return $this->renderForm('users/new.html.twig', [
            'user' => $user,
            'form' => $form,
            'formArgs' => [
                "title" => "Nuevo Usuario",
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_users_show', methods: ['GET'])]
    public function show(Users $user): Response
    {
        return $this->render('users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_users_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Users $user, UsersRepository $usersRepository, UserPasswordHasherInterface $userPasswordHasher, Security $security): Response
    {
        $form = $this->createForm(UsersType::class, $user);
        $form->remove('roles');
        $form->remove('createdAt');
        $form->remove('password');
        $form->remove('active');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $usersRepository->add($user);
//            if ($user->getPassword() !==
//            $userPasswordHasher->hashPassword(
//                $user,
//                $form->get('plainPassword')->getData()
//            )
//            );
            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'formArgs' => [
                "title" => "Editar tu Usuario",
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'])]
    public function delete(Request $request, Users $user, UsersRepository $usersRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $usersRepository->remove($user);
        }

        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/change/password', name: 'app_users_change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, Users $user, UsersRepository $usersRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump("mira mama, se ha submiteado correctemente!");
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setActive(true);
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('index');
        }

        return $this->renderForm('users/change_password.html.twig', [
            'user' => $user,
            'form' => $form,
            'formArgs' => [
                "title" => "Cambiar tu contraseña",
            ]
        ]);
    }
    #[Route('/{id}/change/active', name: 'app_users_change_active', methods: ['GET', 'POST'])]
    public function changeActive(Request $request, Users $user, UsersRepository $usersRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {

            $user->setActive(!$user->getActive());
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }
}
