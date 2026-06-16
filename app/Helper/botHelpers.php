<?php

use App\Model\Coin;
use App\Model\CoinPair;
use App\Model\CurrencyList;
use GuzzleHttp\Client;

function getPriceFromWhiteBitApi($pair)
{
    $response = responseData(false);
    try {
        $client = new Client();
        $callApi2 = $client->get('https://whitebit.com/api/v4/public/ticker');
        $getPriceData = json_decode($callApi2->getBody());
        if (!empty($getPriceData)) {
            if ($getPriceData->$pair) {

                // storeBotException('$callApi2', $getPriceData->$pair->last_price);
                dd($getPriceData->$pair);
            }
        }
        // dd(1);
    } catch (\Exception $e) {
        storeBotException('getPriceFromApi ex -> ' . $pair, $e->getMessage());

        $response = responseData(false, $e->getMessage());
    }
    return $response;
}

function getRandomOptionForSyncPrice()
{
    $options = array(
        BOT_SYNC_MARKET_PRICE,
        BOT_INCREASE_MARKET_PRICE,
        BOT_DECREASE_MARKET_PRICE,
        BOT_RANDOM_MARKET_PRICE
    );
    $randomIndex = array_rand($options);
    return $options[$randomIndex];
}

function getPriceFromApiForBot($pair)
{
    // storeBotException('getPriceFromApiForBot pair', $pair);
    $price = 0;
    $data = getPriceFromApi($pair);
    if ($data['success']) {
        $price = $data['data']['price'];
    }
    // storeBotException('getPriceFromApiForBot rate', $price);
    return $price;
}

function getRandomInt($length, $skip = '')
{
    $result = '';
    $characters = '0123456789';

    if (isset($skip) && $skip >= 0) {
        $characters = str_replace($skip, '', $characters);
    }

    $charactersLength = strlen($characters);

    for ($i = 0; $i < $length; $i++) {
        $result .= $characters[rand(0, $charactersLength - 1)];
    }
    // storeBotException('getRandomInt', $result);
    return $result;
}

function getRandOperation()
{
    $randomOperator = getRandomInt(1);
    if ($randomOperator == 0) {
        return BOT_SYNC_MARKET_PRICE;
    } else {
        $randomOperator = $randomOperator % 2;
        if ($randomOperator > 0) {
            return BOT_INCREASE_MARKET_PRICE;
        } else {
            return BOT_DECREASE_MARKET_PRICE;
        }
    }
}

function getConvertAmount($pair, $from, $to, $amount, $source = RATE_SOURCE_DEFAULT)
{
    $resultAmount = 0;
    $from = strtoupper($from);
    $to = strtoupper($to);
    if ($from == $to) {
        return $amount;
    }
    // storeBotException('getConvertAmount from => ', $from);
    // storeBotException('getConvertAmount to => ', $to);
    // storeBotException('getConvertAmount amount => ', $amount);
    // storeBotException('getConvertAmount source => ', $source);
    $toCurrencyCoinId = null;
    $fromCurrency = Coin::where(['coin_type' => $from])->first();
    $fromCurrencyUsdRate = $fromCurrency->coin_price;
    $fromCurrencyCoinId = $fromCurrency->id;
    $toCurrency = Coin::where(['coin_type' => $to])->first();

    if ($toCurrency) {
        $toCurrencyUsdRate = $toCurrency->coin_price;
        $toCurrencyCoinId = $toCurrency->id;
    } else {
        $toCoin = Coin::where(['coin_type' => 'USDT'])->first();
        if ($toCoin) {
            $toCurrencyUsdRate = $toCoin->coin_price;
        } else {
            $toCurrency = CurrencyList::where(['code' => $to])->first();
            $toCurrencyUsdRate = $toCurrency->rate;
        }
    }
    // storeBotException('getConvertAmount fromCurrencyUsdRate => ', $fromCurrencyUsdRate);
    // storeBotException('getConvertAmount toCurrencyUsdRate => ', $toCurrencyUsdRate);

    if ($source == RATE_SOURCE_DEFAULT) {
        // storeBotException('getConvertAmount source','RATE_SOURCE_DEFAULT');
        $price = 0;
        $baseCoinId = $toCurrencyCoinId;
        $tradeCoinId = $fromCurrencyCoinId;
        if ($baseCoinId && $tradeCoinId) {
            $checkPair = CoinPair::where(['parent_coin_id' => $baseCoinId, 'child_coin_id' => $tradeCoinId])->first();
            if (!$checkPair) {
                $checkReversePair = CoinPair::where(['parent_coin_id' => $tradeCoinId, 'child_coin_id' => $baseCoinId])->first();
                if ($checkReversePair && $checkReversePair->price > 0) {
                    $price = bcdivx(1, $checkReversePair->price, 8);
                }
            } else {
                $price = $checkPair->price;
            }
            // storeBotException('getConvertAmount coin pair price =>',$price);
        }

        if ($price > 0) {
            $resultAmount = bcmulx($amount, $price, 8);
            // storeBotException('getConvertAmount resultAmount =>',$resultAmount);
            return $resultAmount;
        }
    }

    if ($source == RATE_SOURCE_EXTERNAL) {
        // storeBotException('getConvertAmount source','RATE_SOURCE_EXTERNAL');
        // storeBotException('getConvertAmount getPriceFromApiForBot',$from.'_'.$to);
        if ($pair->is_token == STATUS_PENDING) {
            $rate = getPriceFromApiForBot($from . '_' . $to);
            // storeBotException('getConvertAmount rate',$rate);
            if ($rate > 0) {
                $resultAmount = bcmulx($amount, $rate, 8);
                // storeBotException('getConvertAmount resultAmount',$resultAmount);
                return $resultAmount;
            }
        }
    }

    // storeBotException('getConvertAmount $amount',$amount);
    // storeBotException('getConvertAmount $fromCurrencyUsdRate',$fromCurrencyUsdRate);
    // storeBotException('getConvertAmount $toCurrencyUsdRate',$toCurrencyUsdRate);

    $resultAmount = bcmulx($amount, bcdivx($fromCurrencyUsdRate, $toCurrencyUsdRate, 8), 8);
    // storeBotException('getConvertAmount source','RATE_BD');
    // storeBotException('getConvertAmount resultAmount',$resultAmount);
    return $resultAmount;
}

function getRandomIntFromRange($min, $max)
{
    return rand($min, $max);
}


function getLeftSideLength($number)
{
    $numberString = strval($number);
    $decimalIndex = strpos($numberString, '.');

    if ($decimalIndex !== false) {
        return $decimalIndex;
    }

    return strlen($numberString);
}

function generateNumOfZeros($zero_length, $start_num = 1)
{
    $num = strval($start_num);

    for ($i = 0; $i < $zero_length; $i++) {
        $num .= '0';
    }

    return intval($num);
}

function getConsecutiveZeroLength($number)
{
    $decimalPart = explode('.', strval($number))[1] ?? '';

    if ($decimalPart === '') {
        return 0;
    }

    if (preg_match('/^0+/', $decimalPart, $match)) {
        return strlen($match[0]);
    }

    return 0;
}

function getRandomDecimalNumber($intLength = null, $decimal = 8)
{
    if ($intLength === null) {
        $intLength = (int)rand(0, 1);
    }

    $result = rand(0, $intLength);

    if ((int)$result === 0) {
        $result = number_format(mt_rand() / mt_getrandmax(), $decimal, '.', '');
    } else {
        $decimalNum = (int)$result + mt_rand() / mt_getrandmax();
        $result = number_format($decimalNum, $decimal, '.', '');
    }

    // storeBotException('getRandomDecimalNumber', $result);
    return $result;
}

function formatAmountDecimal($amount, $decimal)
{
    return number_format($amount, $decimal);
}

function cleanAndConvertToDecimal($str)
{
    $cleanedStr = str_replace(',', '', $str);
    return floatval($cleanedStr);
}
