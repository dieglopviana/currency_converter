<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{

    public $coins;
    public $feeTicket;
    public $feeCard;
    public $amountMinimunConverionFee;
    public $conversionFee1;
    public $conversionFee2;
    public $coinsSymbol;

    public function __construct(){
        $this->coins = ['BRL', 'USD', 'EUR'];
        $this->feeTicket = (1.45/100);
        $this->feeCard = (7.63/100);
        $this->amountMinimunConverionFee = 3000.00;
        $this->conversionFee1 = (1.00/100);
        $this->conversionFee2 = (2.00/100);

        $this->coinsSymbol = [
            'BRL' => 'R$',
            'USD' => 'US$',
            'EUR' => '€'
        ];
    }


    public function index(){
        // $xmlCoins = Http::get('https://economia.awesomeapi.com.br/xml/available/uniq')
        //     ->getBody()
        //     ->getContents();

        // $coins = new \SimpleXMLElement($xmlCoins);

        return view('currency.index', [
            'coins' => $this->coins
        ]);
    }


    public function converter(Request $request){
        $strAmount    = $request->get('amount');
        $amount       = str_replace(['.', ','], ['', '.'], $strAmount);

        $currencyFrom = $request->get('currencyFrom');
        $currencyTo   = $request->get('currencyTo');
        $paymentType  = $request->get('paymentType');

        // dd([$strAmount, $amount, $currencyFrom, $currencyTo, $paymentType]);

        try {
            $urlQuotation = 'https://economia.awesomeapi.com.br/last/' . $currencyTo . '-' . $currencyFrom;

            $response     = Http::get($urlQuotation);
            $responseJSON = json_decode($response->getBody()->getContents());

            if ($response->successful()){
                $quotation = $responseJSON->{$currencyTo . $currencyFrom};

                $coinsName        = explode('/', $quotation->name);
                $paymentFee       = (($paymentType == 'boleto') ? $amount * $this->feeTicket : $amount * $this->feeCard);
                $conversionFee    = (($amount < $this->amountMinimunConverionFee) ? $amount * $this->conversionFee2 : $amount * $this->conversionFee1);
                $amountConversion = $amount - ($paymentFee + $conversionFee);
                $buyValue         = ($amountConversion / $quotation->bid);

                return response()->json([
                    'currencyFrom' => $currencyFrom,
                    'currencyTo' => $currencyTo,
                    'amount' => $this->coinsSymbol[$currencyFrom] . number_format($amount, 2, ',', '.'),
                    'paymentType' => $paymentType,
                    'quotation' => $this->coinsSymbol[$currencyFrom] . number_format($quotation->bid, 2, ',', '.'),
                    'buyValue' => $this->coinsSymbol[$currencyTo] . number_format($buyValue, 2, ',', '.'),
                    'paymentFee' => $this->coinsSymbol[$currencyFrom] . number_format($paymentFee, 2, ',', '.'),
                    'conversionFee' => $this->coinsSymbol[$currencyFrom] . number_format($conversionFee, 2, ',', '.'),
                    'amountConversion' => $this->coinsSymbol[$currencyFrom] . number_format($amountConversion, 2, ',', '.'),
                ], 200);
            }

            return response()->json([
                'status' => $responseJSON->status,
                'code' => $responseJSON->code,
                'message' => $responseJSON->message
            ], $responseJSON->status);

        } catch (\Exception $e) {
            return response()->json([
                'status' => $e->getCode(),
                'code' => '',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
