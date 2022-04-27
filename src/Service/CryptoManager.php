<?php


namespace App\Service;
use App\Entity\BalanceCoin;
use App\Repository\BalanceCoinRepository;
use App\Repository\CryptoListRepository;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

class CryptoManager
{
    private static $factorUsdEur=0;

    public function __construct(
        private PersistenceManagerRegistry $doctrine,
        private CryptoListRepository $cryptoRepository,
        private BalanceCoinRepository $balanceCoinRepository
    ) {}

    public function addAction($id,$quantity,$quote){
        $entityManager = $this->doctrine->getManager();

        $crypto=$this->cryptoRepository->findOneBy(['ApiCoin_id'=>$id]);
        $balance=new BalanceCoin();
        $balance->setQuote($quote)
            ->setLatestQuote($quote)
            ->setCryptoId($crypto)
            ->setActionClosed(false)
            ->setQuantity($quantity)
            ->setDate(new \DateTime());

        $entityManager->persist($balance);
        $entityManager->flush();
    }

    public function removeAction($id,$quantity,$close){
        $action=$this->balanceCoinRepository->find($id);

        if($action){
            $entityManager = $this->doctrine->getManager();
            if($close){
                $action->setActionClosed(true);
            }else{
                $action->setQuantity($action->getQuantity()-floatval($quantity));
            }
            $entityManager->persist($action);
            $entityManager->flush();
        }

    }

    public static function usdToEur($price): float|int
    {
        @session_start();
        if(!self::$factorUsdEur){
            if(!isset($_SESSION['usdToEurExpiration'],$_SESSION['usdToEurFactor'])||(isset($_SESSION['usdToEurExpiration'])&&$_SESSION['usdToEurExpiration']<time())){
                $_SESSION['usdToEurFactor']=self::xhrUsdEur(); //Enregistre le facteur USD/EUR en session pour une lecture plus rapide
                $_SESSION['usdToEurExpiration']=time()+(60*60); //Conversion USD EUR expire toute les heures
            }
            self::$factorUsdEur=$_SESSION['usdToEurFactor'];
        }
        $result=self::$factorUsdEur*$price;
        return floatval(substr($result,0,strrpos($result,".")+3));
    }

    public static function quotesToPercentage($quote,$latestQuote): float|int
    {
        if($latestQuote==0) return 0;
        return (($latestQuote/$quote)-1)*100;
    }

    private static function xhrUsdEur():float{
        $get = file_get_contents("https://www.google.com/search?q=usd+to+eur",false,null,0,25000);
        $get=substr($get,strpos($get,"</header>"));
        $get=substr($get,0,strpos($get,"Euro"));
        $get=substr($get,strrpos($get,">")+1);
        $get=trim(str_replace(',','.',$get));
        return floatval($get);
    }
}
