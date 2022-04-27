<?php

namespace App\Twig;

use App\Service\CryptoManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ConverterExtension extends  AbstractExtension{

    public function getFilters():array
    {
        return [
            new TwigFilter('formatToEur',[$this,'formatToEur']),
            new TwigFilter('formatToPercent',[$this,'formatToPercent']),
            new TwigFilter('percentageClass',[$this,'percentageClass']),
        ];
    }

    public function formatToPercent(float $percent):string{
        $sign=($percent<0)?"":"+";
        $percent=floatval(substr($percent,0,strrpos($percent,".")+3));
        return "$sign$percent%";
    }
    public function percentageClass(float $percent):string{
        return ($percent<0)?"gain-lose":"gain-win";
    }

    public function formatToEur(float $price):string{
        $sign=($price<0)?"":"+";
        return "$sign ".CryptoManager::usdToEur($price).' €';
    }
}
