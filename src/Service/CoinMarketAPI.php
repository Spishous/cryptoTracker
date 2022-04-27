<?php

namespace App\Service;

use App\Entity\CryptoList;
use App\Entity\Historic;
use App\Repository\BalanceCoinRepository;
use App\Repository\CryptoListRepository;
use App\Repository\HistoricRepository;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CoinMarketAPI extends AbstractController
{
    private const headers =[
        'Accepts: application/json',
        'X-CMC_PRO_API_KEY: b3163f42-6606-4471-8647-da8d1adb922d',
    ];
    private const url='https://pro-api.coinmarketcap.com/v1/cryptocurrency/';
    public static array $arrayCryptoQuotes=[];

    public function __construct(
        private CryptoListRepository $cryptoRepository,
        private HistoricRepository $historicRepository,
        private BalanceCoinRepository $balanceCoinRepository,
        private PersistenceManagerRegistry $doctrine
    )
    {}

    public function getListCrypto(): array
    {
        $CryptoObjects=$this->cryptoRepository->findAll();

        //Save CryptoMap if not exist in database
        if(!$CryptoObjects){
            $this->saveCryptoMapFromAPI();
            $CryptoObjects=$this->cryptoRepository->findAll();
        }
        $CryptoList=[];
        foreach ($CryptoObjects as $CryptoData){
            $CryptoList[]=[
                'id'=>$CryptoData->getApiCoinId(),
                'name'=>$CryptoData->getName(),
                'symbol'=>$CryptoData->getSymbol(),
                'img'=>$this->getImgUrlById($CryptoData->getId())
            ];
        }
        return $CryptoList;
    }

    public static function getImgUrlById($id):string{
        return "https://s2.coinmarketcap.com/static/img/coins/128x128/".$id.".png";
    }

    public function getCryptoMapFromAPI(){
        $response = $this->sendRequest(self::url.'map');
        return json_decode($response,true)['data']??[];
    }

    public function getCryptoQuotesFromAPI(array $listIDArray){
        $listIDString='id='.implode(",",$listIDArray);
        $response=$this->sendRequest(self::url.'quotes/latest',$listIDString);
        return json_decode($response,true)['data']??[];
    }

    public function getGain(bool $forceUpdate=false): array
    {
        if($forceUpdate||$this->historicIsOutdated()){
            return $this->updateHistoric();
        }
        $historic=$this->historicRepository->findOneBy([],['date'=>'DESC']);
        return ['gain'=>$historic->getSum(),'percentage'=>$historic->getPercentage()];
    }

    private static function sendRequest($url,$query=""):string{
        $request = "{$url}?{$query}";
        $curl = curl_init(); // Get cURL resource
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,            // set the request URL
            CURLOPT_HTTPHEADER => self::headers,     // set the headers
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));
        // Send the request, save the response
        return curl_exec($curl);
    }

    #[ArrayShape(['gain' => "float|int", 'percentage' => "float|int"])]
    private function updateHistoric(): array
    {
        $gainArray=$this->calcGain();
        $entityManager = $this->doctrine->getManager();
        $historic=$this->historicRepository->findOneBy(['date'=>date("Y-m-d")]);
        if(!$historic) $historic = new Historic();
        $historic->setDate(date("Y-m-d"))
            ->setSum($gainArray['gain'])
            ->setPercentage($gainArray['percentage']);
        $entityManager->persist($historic);
        $entityManager->flush();
        return $gainArray;
    }

    private function saveCryptoMapFromAPI(){
        $arrayMap = $this->getCryptoMapFromAPI();
        UpdateData::saveCryptoMap($this->doctrine,$arrayMap);
    }

    #[ArrayShape(['gain' => "float|int", 'percentage' => "float|int"])]
    private function calcGain(): array
    {
        $actionsBalance=$this->balanceCoinRepository->findBy(['actionClosed'=>false]);
        $listID=[];
        $currentActionQuote=0;
        $actionsQuotes=0;
        foreach($actionsBalance as $item){
            $actionsQuotes+=$item->getQuantity()*$item->getQuote();
            if(!in_array($item->getCryptoId()->getApiCoinId(),$listID)){
                $listID[]=$item->getCryptoId()->getApiCoinId();
            }
        }

        if(!self::$arrayCryptoQuotes){
            self::$arrayCryptoQuotes=$this->getCryptoQuotesFromAPI($listID);
        }

        foreach($actionsBalance as $action){
            $quote=self::$arrayCryptoQuotes[$action->getCryptoId()->getApiCoinId()]['quote']['USD']['price'];
            $entityManager = $this->doctrine->getManager();
            $action->setLatestQuote($quote);
            $entityManager->persist($action);
            $entityManager->flush();
            $currentActionQuote+=$action->getQuantity()*$action->getLatestQuote();

        }
        $gain=$currentActionQuote-$actionsQuotes;
        $percentage=($currentActionQuote/$actionsQuotes-1)*100;
        return ['gain'=>$gain,'percentage'=>$percentage];
    }

    private function historicIsOutdated(): bool
    {
        $historic=$this->historicRepository->findOneBy(['date'=>date("Y-m-d")]);
        if($historic){
            return false;
        }
        return true;
    }

}
