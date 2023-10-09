<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use GuzzleHttp\Client;

class CurrencyConverterController extends Controller
{
    public function convert(Request $request)
    {
        $amount = $request->input('amount');
        $baseCurrency = 'EUR';
        $targetCurrencies = ['USD', 'AUD', 'CAD', 'CHF', 'MKD', 'JPY'];

        $exchangeRates = $this->getExchangeRates($baseCurrency, $targetCurrencies);

        $convertedValues = [];
        foreach ($targetCurrencies as $currency) {
            if (isset($exchangeRates[$currency])) {
                $convertedValues[$currency] = $amount * $exchangeRates[$currency];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $convertedValues,
        ]);
    }

    private function getExchangeRates($baseCurrency, $targetCurrencies)
    {
        $client = new Client();
        $apiKey = '994289dba30a7f4ac48d182770464a8d'; // My API key

        $response = $client->get("http://api.exchangeratesapi.io/v1/latest?access_key=$apiKey&format=1");

        $data = json_decode($response->getBody(), true);

        if (isset($data['rates'])) {
        return $data['rates'];
        }

        return [];
    }
}
    
    