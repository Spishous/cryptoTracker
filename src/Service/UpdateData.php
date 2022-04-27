<?php


namespace App\Service;


use App\Entity\CryptoList;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UpdateData extends AbstractController
{
    public static function saveCryptoMap(PersistenceManagerRegistry $doctrine, $arrayMap){
        $entityManager = $doctrine->getManager();
        $loop=0;
        foreach ($arrayMap as $e){
            $loop++;
            $newCryptoItem = new CryptoList();
            $newCryptoItem->setName($e['name'])
                ->setApiCoinId($e['id'])
                ->setSymbol($e['symbol']);
            $entityManager->persist($newCryptoItem);
            if($loop>1000){
                $entityManager->flush();
                $loop=0;
            }

        }
        $entityManager->flush();
    }
}
