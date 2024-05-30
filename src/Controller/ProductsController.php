<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Products;
use App\Form\ProductsFormType;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductsController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/products', name: 'products_inicio')]
    public function index(): Response
    {
        $products = $this->em->getRepository(Products::class)->findBy(array(), array('id' => 'desc'));

        return $this->render('/products/products.html.twig', ['products' => $products, 'errors' => array()]);
    }

    #[Route('/products/add', name: 'add_products')]
    public function productos_add(Request $request, ValidatorInterface $validator): Response
    {
        $entity = new Products();

        $form = $this->createForm(ProductsFormType::class, $entity);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted())
        {
            if ($this->isCsrfTokenValid('generico', $submittedToken))
            {
                $errors = $validator->validate($entity);
                
                if (count($errors) > 0)
                {
                    return $this->render('products/add_products.html.twig', ['form' => $form->createView(), 'errors' => $errors]);
                } else
                {
                    $campos = $form->getData();
                    $entity->setName($campos->getName());
                    $entity->setCategory($campos->getCategory());
                    $entity->setCreatedAt(new \DateTime());
                    $entity->setStock($campos->getStock());
                    $this->em->persist($entity);
                    $this->em->flush();

                    $this->addFlash('css', 'success');
                    $this->addFlash('mensaje', 'Se creó el producto correctamente');
                    return $this->redirectToRoute('products_inicio');
                }
            } else 
            {
                $this->addFlash('css', 'warning');
                $this->addFlash('mensaje', 'Ocurrio un error inesperado');
                return $this->redirectToRoute('add_products');
            }
        }
        return $this->render('products/add_products.html.twig', ['form' => $form->createView(), 'errors' => array()]);
    }

    #[Route('/products/edit/{id}', name: 'edit_products')]
    public function productos_edit(Request $request, ValidatorInterface $validator, int $id): Response
    {
        $entity = $this->em->getRepository(Products::class)->find($id);

        $form = $this->createForm(ProductsFormType::class, $entity);
        $form->handleRequest($request);
        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted())
        {
            if ($this->isCsrfTokenValid('generico', $submittedToken))
            {
                $errors = $validator->validate($entity);
                
                if (count($errors) > 0)
                {
                    return $this->render('products/edit_products.html.twig', ['form' => $form->createView(), 'errors' => $errors, 'entity' => $entity]);
                } else
                {
                    $campos = $form->getData();
                    $entity->setName($campos->getName());
                    $entity->setCategory($campos->getCategory());
                    $entity->setCreatedAt(new \DateTime());
                    $entity->setStock($campos->getStock());
                    $this->em->flush();

                    $this->addFlash('css', 'success');
                    $this->addFlash('mensaje', 'Se modifico el producto correctamente');
                    return $this->redirectToRoute('products_inicio');
                }
            } else 
            {
                $this->addFlash('css', 'warning');
                $this->addFlash('mensaje', 'Ocurrio un error inesperado');
                return $this->redirectToRoute('edit_products');
            }
        }
        return $this->render('products/edit_products.html.twig', ['form' => $form->createView(), 'errors' => array(), 'entity' => $entity]);
    }
}
