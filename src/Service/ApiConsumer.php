<?php

declare(strict_types=1);

namespace Eskimi\CommissionTask\Service;

class ApiConsumer
{
    private static string $offlineResponseJson = '{"success":true,"timestamp":1634401804,"base":"EUR","date":"2021-10-16","rates":{"AED":4.260475,"AFN":103.63853,"ALL":121.563315,"AMD":554.92516,"ANG":2.082395,"AOA":693.276623,"ARS":115.003818,"AUD":1.56366,"AWG":2.08842,"AZN":1.976454,"BAM":1.954417,"BBD":2.342355,"BDT":99.274374,"BGN":1.956424,"BHD":0.43721,"BIF":2316.342568,"BMD":1.159911,"BND":1.563827,"BOB":8.016146,"BRL":6.332772,"BSD":1.160086,"BTC":1.9121711e-5,"BTN":86.955092,"BWP":12.990941,"BYN":2.849839,"BYR":22734.258556,"BZD":2.338437,"CAD":1.435657,"CDF":2332.581744,"CHF":1.071064,"CLF":0.034627,"CLP":955.477269,"CNY":7.464961,"COP":4364.873251,"CRC":728.705881,"CUC":1.159911,"CUP":30.737645,"CVE":110.725576,"CZK":25.381002,"DJF":206.139866,"DKK":7.440581,"DOP":65.500639,"DZD":159.152616,"EGP":18.234245,"ERN":17.400104,"ETB":54.11031,"EUR":1,"FJD":2.44282,"FKP":0.850472,"GBP":0.843879,"GEL":3.636368,"GGP":0.850472,"GHS":7.034908,"GIP":0.850472,"GMD":60.315792,"GNF":11251.138575,"GTQ":8.976483,"GYD":242.484775,"HKD":9.021847,"HNL":28.098894,"HRK":7.508574,"HTG":115.433045,"HUF":360.019069,"IDR":16313.512385,"ILS":3.736202,"IMP":0.850472,"INR":87.033979,"IQD":1693.47028,"IRR":48948.250973,"ISK":149.385405,"JEP":0.850472,"JMD":174.378607,"JOD":0.822423,"JPY":132.671222,"KES":128.692588,"KGS":98.364875,"KHR":4732.437902,"KMF":492.556716,"KPW":1043.919688,"KRW":1371.931755,"KWD":0.350027,"KYD":0.966722,"KZT":494.41244,"LAK":11748.740451,"LBP":1767.636008,"LKR":234.335415,"LRD":191.907743,"LSL":17.062736,"LTL":3.424917,"LVL":0.701619,"LYD":5.278037,"MAD":10.504201,"MDL":20.071085,"MGA":4575.849892,"MKD":61.570472,"MMK":2221.547171,"MNT":3306.819056,"MOP":9.295536,"MRO":414.088081,"MUR":49.939716,"MVR":17.921066,"MWK":948.231702,"MXN":23.592133,"MYR":4.822956,"MZN":74.037567,"NAD":17.056538,"NGN":476.573132,"NIO":40.771314,"NOK":9.755497,"NPR":139.126789,"NZD":1.643296,"OMR":0.446568,"PAB":1.160086,"PEN":4.562515,"PGK":4.094921,"PHP":58.819529,"PKR":198.581085,"PLN":4.568832,"PYG":8004.331713,"QAR":4.223281,"RON":4.949229,"RSD":117.495027,"RUB":82.33711,"RWF":1154.111595,"SAR":4.350379,"SBD":9.342858,"SCR":15.612785,"SDG":512.105039,"SEK":10.001618,"SGD":1.563995,"SHP":1.597666,"SLL":12300.858147,"SOS":677.388505,"SRD":24.751929,"STD":24007.81897,"SVC":10.150915,"SYP":1457.975867,"SZL":17.056538,"THB":38.747416,"TJS":13.115815,"TMT":4.04809,"TND":3.275014,"TOP":2.61282,"TRY":10.750525,"TTD":7.886786,"TWD":32.434832,"TZS":2673.595592,"UAH":30.608864,"UGX":4188.072593,"USD":1.159911,"UYU":50.800101,"UZS":12416.849257,"VEF":248024059269.22452,"VND":26400.157748,"VUV":130.253885,"WST":2.989083,"XAF":655.423665,"XAG":0.049751,"XAU":0.000656,"XCD":3.134718,"XDR":0.821432,"XOF":654.190275,"XPF":119.847863,"YER":290.268188,"ZAR":16.827458,"ZMK":10440.596418,"ZMW":19.936736,"ZWL":373.490917}}';

    const API_PATH = 'http://api.exchangeratesapi.io/v1/';
    const ACCESS_KEY = '8d5c4ba3c1de750fea8bf8c2ab5a718a';

    private static function allCurrency(): array
    {
        if (self::checkInternet()) {
            $curlClient = curl_init(self::API_PATH . 'latest' . '?access_key=' . self::ACCESS_KEY . '');
            curl_setopt($curlClient, CURLOPT_RETURNTRANSFER, true);
            $responseJson = curl_exec($curlClient);
            self::$offlineResponseJson = $responseJson;
            curl_close($curlClient);
        } else {
            $responseJson = self::$offlineResponseJson;
        }

        return json_decode($responseJson, true);
    }

    public static function convertCurrency(string $baseCurrency, float $amount): array
    {
        $conversionTable = self::allCurrency();

        if (isset($conversionTable['error'])){
            echo $conversionTable['error']['message'] . 'Loading Data from History.' . PHP_EOL;
            $conversionTable = json_decode(self::$offlineResponseJson);
        }

        $conversionRate = round($conversionTable['rates'][$baseCurrency], 2);

        return [
            'rate' => $conversionRate,
            'amount' => round($amount / $conversionRate, 2)
        ];
    }

    private static function checkInternet(string $domain = 'www.google.com'): bool
    {
        try {
            $connection = @fsockopen($domain, 80);

            return (bool)$connection;
        } catch (\Exception $exception) {
            echo PHP_EOL . 'Internet Connectivity Error: ' . $exception->getMessage() . PHP_EOL;

            return false;
        }
    }
}