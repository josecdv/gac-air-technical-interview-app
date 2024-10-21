<?php

namespace App\Controller;

use App\Entity\Products;
use App\Form\ProductsType;
use App\Form\StockType;
use App\Repository\ProductsRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'app_products_index', methods: ['GET'])]
    public function index(ProductsRepository $productsRepository): Response
    {
        return $this->render('products/index.html.twig', [
            'products' => $productsRepository->findAll(),
            'tableArgs' => [
                "name" => "Productos",
                "btnText" => " Nuevo producto",
                "btnRoute" => "app_products_new",
                ]

        ]);
    }

    #[Route('/new', name: 'app_products_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductsRepository $productsRepository): Response
    {
        $product = new Products();
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);
        $product->setStock(0);
        if ($form->isSubmitted() && $form->isValid()) {
            $productsRepository->add($product);
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('products/new.html.twig', [
            'product' => $product,
            'form' => $form,
            'formArgs' => [
                "title" => "Nuevo producto",
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_products_show', methods: ['GET'])]
    public function show(Products $product): Response
    {
        return $this->render('products/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_products_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Products $product, ProductsRepository $productsRepository): Response
    {
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productsRepository->add($product);
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('products/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'formArgs' => [
                "title" => "Editar producto",
            ]
        ]);
    }

    #[Route('/{id}', name: 'app_products_delete', methods: ['POST'])]
    public function delete(Request $request, Products $product, ProductsRepository $productsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $productsRepository->remove($product);
        }

        return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/substract', name: 'app_products_stock_subtract', methods: ['GET', 'POST'])]
    public function subtract(Request $request, Products $product, ProductsRepository $productsRepository): Response
    {
        $error = null;
        $form = $this->createFormBuilder()
            ->add('stock', NumberType::class, [
                'label' => 'Stock a eliminar'
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->getData()["stock"] > $product->getStock()) {
                $error = "El stock a eliminar no puede ser mayor al stock actual";
            } else {
                $product->setStock($product->getStock() - $form->getData()["stock"]);
                $productsRepository->add($product);
                return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('products/stock.html.twig', [
            'product' => $product,
            'form' => $form,
            'add' => false,
            'error' => $error,
            'formArgs' => [
                "title" => "Eliminar Stock",
            ]

        ]);
    }

    #[Route('/{id}/add', name: 'app_products_stock_add', methods: ['GET', 'POST'])]
    public function add(Request $request, Products $product, ProductsRepository $productsRepository): Response
    {
        $defaultData = ['message' => 'Type your message here'];
        $form = $this->createFormBuilder($defaultData)
            ->add('stock', NumberType::class, [
                'label' => 'Stock a añadir'
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setStock($product->getStock() + $form->getData()["stock"]);
            $productsRepository->add($product);
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('products/stock.html.twig', [
            'product' => $product,
            'form' => $form,
            'add' => true,
            'error' => null,
            'formArgs' => [
                "title" => "Añadir Stock",
            ]
        ]);
    }
}
