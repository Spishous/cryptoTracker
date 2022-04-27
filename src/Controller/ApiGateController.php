<?php

namespace App\Controller;

use App\Service\CoinMarketAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\HistoricRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;

class ApiGateController extends AbstractController
{
    /**
     * @Route("/crypto-api/{id}",name="crypto-api", requirements={"id"="\d+"})
     */
    public function api($id,CoinMarketAPI $coinMarketAPI): Response{

        $data=$coinMarketAPI->getCryptoQuotesFromAPI([$id]);
        return $this->json($data);
    }
}
