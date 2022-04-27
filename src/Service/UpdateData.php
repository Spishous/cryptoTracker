<?php


namespace App\Service;


use App\Entity\CryptoList;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UpdateData extends AbstractController
{
    public static function saveCryptoMap(PersistenceManagerRegistry $doctrine, $arrayMap){
        $entityManager = $doctrine->getManager();
        foreach ($arrayMap as $e){
            $newCryptoItem = new CryptoList();
            $newCryptoItem->setName($e['name'])
                ->setApiCoinId($e['id'])
                ->setSymbol($e['symbol']);
            $entityManager->persist($newCryptoItem);
        }
        $entityManager->flush();
    }
}
