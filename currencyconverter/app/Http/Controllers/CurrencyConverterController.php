<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class CurrencyConverterController extends Controller
{
    public function convert(Request $request)
    {
        try {
            $amount = $request->input('amount');

            // Validate the amount
            if (!is_numeric($amount) || $amount <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid amount. Please enter valid amount for conversion.',
                ]);
            }

            $baseCurrency = 'EUR';
            $targetCurrencies = ['USD', 'AUD', 'CAD', 'CHF', 'MKD', 'JPY'];

            $exchangeRates = $this->getExchangeRates($baseCurrency, $targetCurrencies);

            if (empty($exchangeRates)) {
                // Handle empty exchange rates data
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to fetch exchange rates data.',
                ]);
            }

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
        } catch (\Exception $e) {
            // Handle any general exceptions
            Log::error('Error in CurrencyConverterController: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing the request.',
            ]);
        }
    }

    private function getExchangeRates($baseCurrency, $targetCurrencies)
    {
        try {
            $client = new Client();
            $apiKey = '994289dba30a7f4ac48d182770464a8d'; // My API key

            $response = $client->get("http://api.exchangeratesapi.io/v1/latest?access_key=$apiKey&format=1");

            $data = json_decode($response->getBody(), true);

            if (isset($data['rates'])) {
                return $data['rates'];
            } else {
                // Handle missing rates data
                return [];
            }
        } catch (RequestException $e) {
            // Handle Guzzle HTTP request exception
            Log::error('Error in CurrencyConverterController (HTTP): ' . $e->getMessage());
            return [];
        } catch (\Exception $e) {
            // Handle any other exceptions
            Log::error('Error in CurrencyConverterController: ' . $e->getMessage());
            return [];
        }
    }
}