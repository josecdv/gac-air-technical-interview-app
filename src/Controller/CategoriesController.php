<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Form\CategoriesFormType;
use App\Entity\Categories;

class CategoriesController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/categories', name: 'categories_inicio')]
    public function index(): Response
    {
        $categories = $this->em->getRepository(Categories::class)->findBy(array(), array('id' => 'desc'));

        return $this->render('categories/categories.html.twig', ['categories' => $categories, 'errors' => array()]);
    }

    #[Route('/categories/add', name: 'add_categories')]
    public function categorias_add(Request $request, ValidatorInterface $validator): Response
    {
        $entity = new Categories();

        $form = $this->createForm(CategoriesFormType::class, $entity);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted())
        {
            if ($this->isCsrfTokenValid('generico', $submittedToken))
            {
                $errors = $validator->validate($entity);
                if (count($errors) > 0)
                {
                    return $this->render('categories/add_category.html.twig', ['form' => $form->createView(), 'errors' => $errors]);
                } else
                {
                    $campos = $form->getData();
                    $entity->setName($campos->getName());
                    $entity->setCreatedAt(new \DateTime());
                    $this->em->persist($entity);
                    $this->em->flush();

                    $this->addFlash('css', 'success');
                    $this->addFlash('mensaje', 'Se creó el usuario correctamente');

                    return $this->redirectToRoute('categories_inicio');
                }
            } else
            {
                $this->addFlash('css', 'warning');
                $this->addFlash('mensaje', 'Ocurrio un error inesperado');
                return $this->redirectToRoute('add_categories');
            }
        }

        return $this->render('categories/add_category.html.twig', ['form' => $form->createView(), 'errors' => array()]);
    }

    #[Route('/categories/edit/{id}', name: 'edit_categories')]
    public function categories_edit(Request $request, ValidatorInterface $validator, int $id): Response
    {
        $entity = $this->em->getRepository(Categories::class)->find($id);

        $form = $this->createForm(CategoriesFormType::class, $entity);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted())
        {
            if ($this->isCsrfTokenValid('generico', $submittedToken))
            {
                $errors = $validator->validate($entity);
                if (count($errors) > 0)
                {
                    return $this->render('categories/edit_category.html.twig', ['form' => $form->createView(), 'errors' => $errors, 'entity' => $entity]);
                } else
                {
                    $campos = $form->getData();
                    $entity->setName($campos->getName());
                    $entity->setCreatedAt(new \DateTime());
                    $this->em->flush();
                    
                    $this->addFlash('css', 'success');
                    $this->addFlash('mensaje', 'Se modifico la categoria correctamente');

                    return $this->redirectToRoute('categories_inicio');
                }
            } else
            {
                $this->addFlash('css', 'warning');
                $this->addFlash('mensaje', 'Ocurrio un error inesperado');
                return $this->redirectToRoute('edit_categories');
            }
        }

        return $this->render('categories/edit_category.html.twig', ['form' => $form->createView(), 'errors' => array(), 'entity' => $entity]);
    }

    #[Route('/categories/delete/{id}', name: 'delete_categories')]
    public function delete_categories(Request $request, int $id)
    {
        $entity = $this->em->getRepository(Categories::class)->find($id);

        $this->em->remove($entity);
        $this->em->flush();

        $this->addFlash('css', 'success');
        $this->addFlash('mensaje', 'Se eliminó la categoria correctamente');
        return $this->redirectToRoute('categories_inicio');
    }
}
