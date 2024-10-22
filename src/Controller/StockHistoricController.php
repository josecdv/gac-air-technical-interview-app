<?php

namespace App\Controller;

use App\Entity\StockHistoric;
use App\Form\StockHistoricType;
use App\Repository\StockHistoricRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/historic/{id}')]
class StockHistoricController extends AbstractController
{
    #[Route('/', name: 'app_stock_historic_index', methods: ['GET'])]
    public function index(StockHistoricRepository $stockHistoricRepository, $id): Response
    {
        $stockHistoric = $stockHistoricRepository->findBy(["product" => $id]);
        return $this->render('stock_historic/index.html.twig', [
            'stock_historics' => $stockHistoric,
            'tableArgs' => [
                "name" => "Histórico del Artículo: " . reset($stockHistoric)->getProduct()->getName(),
                "btnText" => " Nuevo usuario",
                "btnRoute" => null,
            ]
        ]);
    }
}
