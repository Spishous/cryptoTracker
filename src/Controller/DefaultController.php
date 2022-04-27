<?php

namespace App\Controller;

use App\Entity\BalanceCoin;
use App\Repository\BalanceCoinRepository;
use App\Repository\CryptoListRepository;
use App\Repository\CryptoUpdateRepository;
use App\Repository\HistoricRepository;
use App\Service\CryptoManager;
use App\Service\CoinMarketAPI;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(BalanceCoinRepository $balanceCoinRepository,CoinMarketAPI $coinMarketAPI): Response
    {
        $gain=0;
        $gainPercentage=0;
        $list=[];
        $balanceList=$balanceCoinRepository->findBy(['actionClosed'=>false]);
        if(isset($balanceList)&&$balanceList){
            $gainArray=$coinMarketAPI->getGain();
            $gain=$gainArray['gain'];
            $gainPercentage=$gainArray['percentage'];

            foreach($balanceList as $balance){
                $list[]=[
                    "id"=>$balance->getId(),
                    "img"=>CoinMarketAPI::getImgUrlById($balance->getCryptoId()->getApiCoinId()),
                    "name"=>$balance->getCryptoId()->getName(),
                    "symbol"=>$balance->getCryptoId()->getSymbol(),
                    "quantity"=>$balance->getQuantity(),
                    "percentage"=>CryptoManager::quotesToPercentage($balance->getQuote(),$balance->getLatestQuote())
                        ];
            }
        }
        return $this->render("app/home.html.twig", [
            'gain'=>$gain,
            'percentage'=>$gainPercentage,
            'list'=>$list
        ]);
    }

    /**
     * @Route("/add", methods={"POST"})
     */
    public function _add(Request $request, CryptoManager $balance, CoinMarketAPI $coinMarketAPI)
    {
        $balance->addAction(
            $request->request->getInt('crypto'),
            floatval($request->request->get('quantity')),
            floatval($request->request->get('quote'))
        );
        $coinMarketAPI->getGain(true);
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/add", name="add", methods={"GET"})
     */
    public function add(CoinMarketAPI $coinMarketAPI): Response
    {
        $list=$coinMarketAPI->getListCrypto();
        return $this->render("app/add.html.twig", [
            'list' => $list,
        ]);
    }

    /**
     * @Route("/remove/{id}", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function _remove($id,Request $request, CryptoManager $balance, CoinMarketAPI $coinMarketAPI){
        $quantity=$request->request->get('quantity');
        $closeAction=$request->request->getBoolean('closeAction');

        $balance->removeAction($id,$quantity,$closeAction);
        $coinMarketAPI->getGain(true);
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/remove/{id}", name="remove", requirements={"id"="\d+"})
     */
    public function remove($id,Request $request,BalanceCoinRepository $balanceCoinRepository,CoinMarketAPI $coinMarketAPI): Response
    {
        if($id===0){
            return $this->redirectToRoute('home');
        }
        $coinMarketAPI->getGain(true);
        $action=$balanceCoinRepository->findOneBy(['actionClosed'=>false,'id'=>$id]);
        if($action){
            return $this->render("app/remove.html.twig",[
                "usdEurFactor"=>CryptoManager::usdToEur(1),
                "quantityMax"=>$action->getQuantity(),
                "id"=>$id,
                "name"=>$action->getCryptoId()->getName(),
                "symbol"=>$action->getCryptoId()->getSymbol(),
                "quote"=>$action->getQuote(),
                "latestQuote"=>$action->getLatestQuote(),
                "percentage"=>CryptoManager::quotesToPercentage($action->getQuote(),$action->getLatestQuote())
            ]);
        }else{
            return $this->redirectToRoute('home');
        }

    }

    /**
     * @Route("/preview", name="preview")
     */
    public function preview(ChartBuilderInterface $chartBuilder,HistoricRepository $historicRepository): Response
    {
        $delay="day";
        $nStep=8;
        $labels=[];
        $datasets=[];
        $val=0;
        foreach(range(0,$nStep) as $loop){
            $date=strtotime("-".($nStep-$loop)." $delay",time());
            $labels[]=date("d/m",$date);
            $hist=$historicRepository->findFirstDateBefore(date("Y-m-d H:i:s",$date));
            $val=($hist!=null)?CryptoManager::usdToEur($hist->getSum()):$val;
            $datasets[]=$val;
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'CryptoManager (€)',
                    'backgroundColor' => '#1fc36c',
                    'borderColor' => '#1fc36c',
                    'data' => $datasets,
                ]
            ],
        ]);

        return $this->render("app/preview.html.twig", [
            'chart' => $chart,
        ]);
    }
}
