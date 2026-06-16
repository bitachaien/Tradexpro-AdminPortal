<?php

use App\User;
use App\Model\Buy;
use Carbon\Carbon;
use Pusher\Pusher;
use App\Model\Coin;
use App\Model\Sell;
use App\Model\Wallet;
use GuzzleHttp\Client;
use App\Model\CoinPair;
use App\Model\ThemeColor;
use App\Model\UserWallet;
use App\Jobs\BroadcastJob;
use App\Model\ActivityLog;
use App\Model\CountryList;
use App\Model\Transaction;
use App\Model\AdminSetting;
use App\Model\CurrencyList;
use App\Model\FutureWallet;
use Jenssegers\Agent\Agent;
use App\Http\Services\Logger;
use Illuminate\Session\Store;
use App\Model\WithdrawHistory;
use Illuminate\Http\JsonResponse;
use App\Http\Services\CoinService;
use App\Model\DepositeTransaction;
use App\Model\VerificationDetails;
use Illuminate\Support\Facades\DB;
use App\Model\FutureTradeLongShort;
use App\Model\ThirdPartyKycDetails;
use App\Model\WalletAddressHistory;
use Illuminate\Support\Facades\Log;
use App\Http\Services\ERC20TokenApi;
use App\Model\CoinPaymentNetworkFee;
use Intervention\Image\ImageManager;
use \Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use App\Http\Services\BuyOrderService;
use App\Http\Services\CoinPaymentsAPI;
use App\Http\Services\DashboardService;
use App\Http\Services\SellOrderService;
use Illuminate\Support\Facades\Storage;
use App\Http\Services\BitCoinApiService;
use App\Http\Services\BitgoWalletService;
use Intervention\Image\Drivers\Gd\Driver;
use App\Model\CurrencyDepositPaymentMethod;
use App\Model\FutureTradeTransactionHistory;
use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\CoinPairRepository;
use App\Http\Repositories\DashboardRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\UserWalletRepository;

require_once('rolePermission.php');

/**
 * @param $role_task
 * @param $my_role
 * @return int
 */

function previousMonthName($m)
{
    $months = [];
    for ($i = $m; $i >= 0; $i--) {
        array_push($months, date('F', strtotime('-' . $i . ' Month')));
    }

    return array_reverse($months);
}
function previousYearMonthName()
{

    $months = [];
    for ($i = 0; $i < 12; $i++) {

        array_push($months, Carbon::now()->startOfYear()->addMonth($i)->format('F'));
    }

    return $months;
}

function previousDayName()
{
    $days = array();
    for ($i = 1; $i < 8; $i++) {
        array_push($days, Carbon::now()->startOfWeek()->subDays($i)->format('l'));
    }

    return array_reverse($days);
}
function previousMonthDateName()
{
    $days = array();
    for ($i = 0; $i < 30; $i++) {
        array_push($days, Carbon::now()->startOfMonth()->addDay($i)->format('d-M'));
    }

    return $days;
}

function allsetting($settings = null)
{
    if ($settings == null || !isset($settings[0])) {
        $allSettings = AdminSetting::pluck('value', 'slug')->toArray();
        return $allSettings ?? false;
    } elseif (is_array($settings)) {
        $allSettings = AdminSetting::whereIn('slug', $settings)->pluck('value', 'slug')->toArray();
        return $allSettings ?? false;
    } else {
        $allSettings = AdminSetting::where(['slug' => $settings])->first();
        return @$allSettings->value ?? false;
    }
}

/**
 * @param null $input
 * @return array|mixed
 */

function addActivityLog($action, $source, $ip_address, $location)
{
    $return = false;
    if (ActivityLog::create(['action' => $action, 'user_id' => $source, 'ip_address' => $ip_address, 'location' => $location]))
        $return = true;
    return $return;
}

function country($input = null)
{

    if (is_null($input)) {
        return CountryList::where('status', STATUS_ACTIVE)->pluck('value', 'key');
    } else {

        return CountryList::where('key', $input)->pluck('value')->first();
    }
}

/**
 * @param $registrationIds
 * @param $type
 * @param $data_id
 * @param $count
 * @param $message
 * @return array
 */
//google firebase
function pushNotification($registrationIds, $type, $data_id, $count, $message)
{

    // $news = \App\News::find($data_id);
    $fields = array(
        'to' => $registrationIds,
        "delay_while_idle" => true,
        "time_to_live" => 3,
        /*    'notification' => [
                'body' => strip_tags(str_limit($news->description,30)),
                'title' => str_limit($news->title,25),
            ],*/
        'data' => [
            'message' => $message,
            'title' => 'monttra',
            'id' => $data_id,
            'is_background' => true,
            'content_available' => true,

        ]
    );


    $headers = array(
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);

    return $fields;
}


/**
 * @param $registrationIds
 * @param $type
 * @param $data_id
 * @param $count
 * @param $message
 * @return array
 */
//google firebase
function pushNotificationIos($registrationIds, $type, $data_id, $count, $message)
{

    //    $news = \App\News::find($data_id);

    $fields = array(
        'to' => $registrationIds,
        "delay_while_idle" => true,

        "time_to_live" => 3,
        'notification' => [
            'body' => '',
            'title' => $message,
            'vibrate' => 1,
            'sound' => 'default',
        ],
        'data' => [
            'message' => '',
            'title' => $message,
            'id' => $data_id,
            'is_background' => true,
            'content_available' => true,


        ]
    );

    $headers = array(
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);

    return $fields;
}





/**
 * @param $a
 * @return string
 */
//Random string
function randomString($a)
{
    $x = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $c = strlen($x) - 1;
    $z = '';
    for ($i = 0; $i < $a; $i++) {
        $y = rand(0, $c);
        $z .= substr($x, $y, 1);
    }
    return $z;
}

/**
 * @param int $a
 * @return string
 */
// random number
function randomNumber($a = 10)
{
    $x = '123456789';
    $c = strlen($x) - 1;
    $z = '';
    for ($i = 0; $i < $a; $i++) {
        $y = rand(0, $c);
        $z .= substr($x, $y, 1);
    }
    return $z;
}

//use array key for validator
/**
 * @param $array
 * @param string $seperator
 * @param array $exception
 * @return string
 */
function arrKeyOnly($array, $seperator = ',', $exception = [])
{
    $string = '';
    $sep = '';
    foreach ($array as $key => $val) {
        if (in_array($key, $exception) == false) {
            $string .= $sep . $key;
            $sep = $seperator;
        }
    }
    return $string;
}

/**
 * @param $img
 * @param $path
 * @param null $user_file_name
 * @param null $width
 * @param null $height
 * @return bool|string
 */
function uploadInStorage($img, $path, $user_file_name = null, $width = null, $height = null)
{
    $imgManager = new ImageManager(new Driver());
    if (!file_exists($path)) {

        mkdir($path, 777, true);
    }

    if (isset($user_file_name) && $user_file_name != "" && file_exists($path . $user_file_name)) {
        unlink($path . $user_file_name);
    }
    // saving image in target path
    $imgName = uniqid() . '.' . $img->getClientOriginalExtension();
    $imgPath = public_path($path . $imgName);
    // making image
    $makeImg = $imgManager->read($img);
    if ($width != null && $height != null && is_int($width) && is_int($height)) {
        // $makeImg->resize($width, $height);
        // $makeImg->fit($width, $height);
        $makeImg->scale($width, $height);
    }

    // if ($makeImg->save($imgPath)) {
    $makeImg->toPng()->save($imgPath);
    return $imgName;
}

function uploadimage($img, $path, $user_file_name = null, $width = null, $height = null)
{
    $imgManager = new ImageManager(new Driver());
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    if (isset($user_file_name) && $user_file_name != "" && file_exists($path . $user_file_name)) {
        unlink($path . $user_file_name);
    }
    // saving image in target path
    $imgName = uniqid() . '.' . $img->getClientOriginalExtension();
    $imgPath = public_path($path . $imgName);
    // making image
    $makeImg = $imgManager->read($img);
    if ($width != null && $height != null && is_int($width) && is_int($height)) {
        // $makeImg->resize($width, $height);
        $makeImg->scale($width, $height);
    }

    $makeImg->toPng()->save($imgPath);
    return $imgName;
}


/**
 * @param $path
 * @param $file_name
 */
function removeImage($path, $file_name)
{
    if (isset($file_name) && $file_name != "" && file_exists($path . $file_name)) {
        unlink($path . $file_name);
    }
}

//Advertisement image path
/**
 * @return string
 */
function path_image()
{
    return IMG_VIEW_PATH;
}

/**
 * @return string
 */
function upload_path()
{
    return 'uploads/';
}



/**
 * @param $file
 * @param $destinationPath
 * @param null $oldFile
 * @return bool|string
 */
function uploadFile($new_file, $path, $old_file_name = null, $width = null, $height = null)
{
    $imgManager = new ImageManager(new Driver());
    if (!file_exists(public_path($path))) {
        mkdir(public_path($path), 0777, true);
    }
    if (isset($old_file_name) && $old_file_name != "" && file_exists($path . substr($old_file_name, strrpos($old_file_name, '/') + 1))) {

        unlink($path . '/' . substr($old_file_name, strrpos($old_file_name, '/') + 1));
    }

    $input['imagename'] = uniqid() . time() . '.' . $new_file->getClientOriginalExtension();
    $imgPath = public_path($path . $input['imagename']);

    if ($new_file->getClientOriginalExtension() == "gif") {
        $new_file->move(public_path($path), $input['imagename']);
        return $input['imagename'];
    } else {
        $makeImg = $imgManager->read($new_file);
        if ($width != null && $height != null && is_int($width) && is_int($height)) {
            // $makeImg->resize($width, $height);
            // $makeImg->fit($width, $height);
            $makeImg->scale($width, $height);
        }

        $makeImg->toPng()->save($imgPath);
        return $input['imagename'];
    }
    return false;
}

function containsWord($str, $word)
{
    return !!preg_match('#\\b' . preg_quote($word, '#') . '\\b#i', $str);
}

/**
 * @param $destinationPath
 * @param $file
 */
function deleteFile($destinationPath, $file)
{
    if (isset($file) && $file != "" && file_exists($destinationPath . $file)) {
        unlink($destinationPath . $file);
    }
}

//function for getting client ip address
/**
 * @return mixed|string
 */
function get_clientIp()
{
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

/**
 * @return array|bool
 */
function language()
{
    $lang = [];
    $path = base_path('resources/lang');
    foreach (glob($path . '/*.json') as $file) {
        $langName = basename($file, '.json');
        $lang[$langName] = $langName;
    }
    return empty($lang) ? false : $lang;
}

/**
 * @param null $input
 * @return array|mixed
 */
function langName($input = null)
{
    $output = [
        'ar' => 'Arabic',
        'de' => 'German',
        'en' => 'English',
        'es' => 'Spanish',
        'et' => 'Estonian',
        'fr' => 'French',
        'it' => 'Italian',
        'pl' => 'Polish',
        'pt' => 'Portuguese (European)',
        'pt-br' => 'Portuguese (Brazil)',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'zh-CN' => 'Chinese (Simplified)',
        'zh-TW' => 'Chinese (Traditional)',
        'zh-HK' => 'Chinese (Hong Kong)',
        'zh-SG' => 'Chinese (Singapore)',
        'zh' => 'Chinese (Singapore)',
        'ko' => 'Korean',
        'ja' => 'Japanese',
        'nl' => 'Dutch',
        'id' => 'Indonesian',
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return $output[$input];
    }
}

function langNameOld($input = null)
{
    $output = [
        'ar' => 'Arabic',
        'de' => 'German',
        'en' => 'English',
        'es' => 'Spanish',
        'et' => 'Estonian',
        'fr' => 'French',
        'it' => 'Italian',
        'pl' => 'Polish',
        'pt' => 'Portuguese (European)',
        'pt-br' => 'Portuguese (Brazil)',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'zh-CN' => 'Chinese (Simplified)',
        'zh-TW' => 'Chinese (Traditional)',
        'zh-HK' => 'Chinese (Hong Kong)',
        'zh-SG' => 'Chinese (Singapore)',
        'zh' => 'Chinese (Singapore)',
        'ko' => 'Korean',
        'ja' => 'Japanese',
        'nl' => 'Dutch',
        'id' => 'Indonesian',
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return $output[$input];
    }
}

/**
 * @param null $input
 * @return array|mixed|string
 */
function langNameMobile($input = null)
{
    $output = [
        'en' => 'English',
        'fr' => 'French',
        'it' => 'Italian',
        'pt-PT' => ' Português(Portugal)',
    ];
    if (is_null($input)) {
        return $output;
    } else {
        if (isset($output[$input]))
            return $output[$input];
        return '';
    }
}

if (!function_exists('settings')) {

    function settings($keys = null)
    {
        if ($keys && is_array($keys)) {
            return AdminSetting::whereIn('slug', $keys)->pluck('value', 'slug')->toArray();
        } elseif ($keys && is_string($keys)) {
            $setting = AdminSetting::where('slug', $keys)->first();
            return empty($setting) ? false : $setting->value;
        }
        return AdminSetting::pluck('value', 'slug')->toArray();
    }
}

function landingPageImage($index, $static_path)
{
    if (settings($index)) {
        return asset(path_image()) . '/' . settings($index);
    }
    return asset('assets/landing') . '/' . $static_path;
}

//Call this in every function
/**
 * @param $lang
 */
function set_lang($lang)
{
    $default = settings('lang');
    $lang = strtolower($lang);
    $languages = language();
    if (in_array($lang, $languages)) {
        app()->setLocale($lang);
    } else {
        if (isset($default)) {
            $lang = $default;
            app()->setLocale($lang);
        }
    }
}

/**
 * @param null $input
 * @return array|mixed
 */
function langflug($input = null)
{

    $output = [
        'en' => '<i class="flag-icon flag-icon-us"></i> ',
        'pt-PT' => '<i class="flag-icon flag-icon-pt"></i>',
        'fr' => '<i class="flag-icon flag-icon-fr"></i>',
        'it' => '<i class="flag-icon flag-icon-it"></i>',
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return $output[$input];
    }
}


//find odd even
/**
 * @param $number
 * @return string
 */
function oddEven($number)
{
    //    dd($number);
    if ($number % 2 == 0) {
        return 'even';
    } else {
        return 'odd';
    }
}

// get user currency
function getUserCurrency()
{
    if (Auth::user()) {
        $currency = Auth::user()->currency;
    } else {
        $currency = settings('currency');
    }
    return $currency;
}
function toCoinPrice($coinType)
{
    $coinPrice = 1;
    $coin = Coin::where(['coin_type' => $coinType])->first();
    if (!empty($coin)) {
        $coinPrice = $coin->coin_price == 0 ? 1 : $coin->coin_price;
    }
    return $coinPrice;
}

function checkTwoFactor($type, $request)
{
    $settings = settings();
    if (filter_var($settings[$type], FILTER_VALIDATE_BOOLEAN)) {
        $two_factor_service = new App\Http\Services\User2FAService();
        $response = $two_factor_service->userOtpVerification($request, Auth::user());
        return $response;
    }
    return ["success" => false, "massage" => __("Two factor verification failed!!")];
}

function fromCoinPrice($coinType)
{
    $coinPrice = 0;
    $coin = Coin::where(['coin_type' => $coinType])->first();
    if (!empty($coin)) {
        $coinPrice = $coin->coin_price == 0 ? 1 : $coin->coin_price;
    }
    return $coinPrice;
}
// get own site market rate
function getOwnMarketRate($from, $to)
{
    $rate = 0;
    try {
        $tradeCoinId = get_coin_id($from);
        $baseCoinId = get_coin_id($to);

        $fromCoinPrice = fromCoinPrice($from);
        $toCoinPrice = toCoinPrice($to);
        $rate = bcdivx($fromCoinPrice, $toCoinPrice, 8);

        $repo = new CoinPairRepository(CoinPair::class);
        $pair = $repo->getCoinPairsData($baseCoinId, $tradeCoinId);
        if (!empty($pair) && ($pair->last_price > 0)) {
            $rate = $pair->last_price;
        } else {
            $reversePair = $repo->getCoinPairsData($tradeCoinId, $baseCoinId);
            if (!empty($reversePair) && ($reversePair->last_price > 0)) {
                $rate = bcdivx(1, $reversePair->last_price, 8);
            } else {
                $baseCoinIdNew = get_coin_id('USDT');
                $pairOne = $repo->getCoinPairsData($baseCoinIdNew, $tradeCoinId);
                $pairTwo = $repo->getCoinPairsData($baseCoinIdNew, $baseCoinId);
                if (!empty($pairOne) && !empty($pairTwo)) {
                    $rate = bcdivx($pairOne->last_price, $pairTwo->last_price, 8);
                }
            }
        }
    } catch (\Exception $e) {
        storeException('getOwnMarketRate', $e->getMessage());
    }

    return $rate;
}


function convert_currency($amount, $to = 'USD', $from = 'BTC', $currency = NULL)
{
    $returnAmountData = 0;
    try {
        $toCoin = Coin::where(['coin_type' => $to])->first();
        $fromCoin = Coin::where(['coin_type' => $from])->first();
        if (!empty($toCoin) && !empty($fromCoin)) {        // coin to coin
            $rate = getOwnMarketRate($fromCoin->coin_type, $toCoin->coin_type);
        } elseif (!empty($fromCoin) && empty($toCoin)) {    // coin to currency
            $coinToCurrency = getOwnMarketRate($fromCoin->coin_type, 'USDT');
            $rate = convert_fiat_currency($fromCoin->coin_type, $to, $coinToCurrency, CONVERT_TYPE_CRYPTO_TO_FIAT);
        } elseif (empty($fromCoin) && !empty($toCoin)) {    // currency to coin
            $coinToCurrency = getOwnMarketRate($toCoin->coin_type, 'USDT');
            $rate = convert_fiat_currency($toCoin->coin_type, $from, $coinToCurrency, CONVERT_TYPE_FIAT_TO_CRYPTO);
        } else {                                           // currency to currency
            $rate = convert_fiat_currency($from, $to, 1, CONVERT_TYPE_FIAT_TO_FIAT);
        }
        $returnAmountData = bcmulx($rate, $amount, 8);
        return $returnAmountData;
    } catch (\Exception $e) {
        storeException('convert_currency', $e->getMessage());
    }
    return $returnAmountData;
}

// get fiat currency rate
function convert_fiat_currency($from, $to, $amount, $type)
{
    try {
        $returnAmount = 0;
        $toCoinRate = toCoinRate($to);
        if ($type == CONVERT_TYPE_CRYPTO_TO_FIAT) {
            $returnAmount = bcmulx($toCoinRate, $amount, 8);
        } elseif ($type == CONVERT_TYPE_FIAT_TO_CRYPTO) {
            $returnAmount = bcdivx(1, bcmulx($toCoinRate, $amount, 8), 8);
        } else {
            $fromCoinRate = toCoinRate($to);
            $returnAmount = bcmulx(1, bcdivx($toCoinRate, $fromCoinRate, 8), 8);
        }
        return $returnAmount;
    } catch (\Exception $e) {
        storeException('convert_fiat_currency ', $e->getMessage());
        $returnAmount = 0;
    }
    return $returnAmount;
}

// convert fiat to fiat
function convert_fiat_to_fiat() {}

function fromCoinRate($from)
{
    $fromCoin = CurrencyList::where(['code' => strtoupper($from)])->first();
    if (empty($fromCoin)) {
        $fromCoinRate = 1;
    } else {
        if ($fromCoin->rate == 0) {
            $fromCoinRate = 1;
        } else {
            $fromCoinRate = $fromCoin->rate;
        }
    }
    return $fromCoinRate;
}

function toCoinRate($to)
{
    $toCoin = CurrencyList::where(['code' => strtoupper($to)])->first();
    if (empty($toCoin)) {
        $toCoinRate = 1;
    } else {
        if ($toCoin->rate == 0) {
            $toCoinRate = 1;
        } else {
            $toCoinRate = $toCoin->rate;
        }
    }
    return $toCoinRate;
}

function convert_currency_rate($amount, $to = 'USD', $from = 'BTC')
{
    $to = check_default_coin_type($to);
    $from = check_default_coin_type($from);
    $apiKey = env('CRYPTOCOMPARE_API_KEY') ?? '';
    $url = "https://min-api.cryptocompare.com/data/price?fsym=$from&tsyms=$to&api_key=$apiKey";
    $json = file_get_contents($url); //,FALSE,$ctx);
    $jsondata = json_decode($json, TRUE);

    if (isset($jsondata['Response']) && $jsondata['Response'] == 'Error') {
        storeBotException('convert_currency error ->', $jsondata['Message']);
        $returnAmount = 0;
    } else {
        $returnAmount = bcmulx($amount, custom_number_format($jsondata[$to]), 8);
    }
    return $returnAmount;
}

function convert_currency_rate_all($data, $currency = 'USD')
{
    $returnData = [];
    $apiKey = env('CRYPTOCOMPARE_API_KEY') ?? '';
    $url = "https://min-api.cryptocompare.com/data/pricemulti?fsyms=$data&tsyms=" . $currency . "&api_key=$apiKey";
    $json = file_get_contents($url); //,FALSE,$ctx);
    $jsondata = json_decode($json, TRUE);

    if (isset($jsondata['Response']) && $jsondata['Response'] == 'Error') {
        storeException('convert_currency_rate_all error ->', $jsondata['Message']);
    } else {
        $returnData = $jsondata;
    }
    return $returnData;
}
// fees calculation
function calculate_fees($amount, $method)
{
    $settings = allsetting();

    try {
        if ($method == SEND_FEES_FIXED) {
            return $settings['send_fees_fixed'];
        } elseif ($method == SEND_FEES_PERCENTAGE) {
            return ($settings['send_fees_percentage'] * $amount) / 100;
        } else {
            return 0;
        }
    } catch (\Exception $e) {
        return 0;
    }
}

/**
 * @param null $message
 * @return string
 */
function getToastrMessage($message = null)
{
    if (!empty($message)) {

        // example
        // return redirect()->back()->with('message','warning:Invalid username or password');

        $message = explode(':', $message);
        if (isset($message[1])) {
            $data = 'toastr.' . $message[0] . '("' . $message[1] . '")';
        } else {
            $data = "toastr.error(' write ( errorType:message ) ')";
        }

        return '<script>' . $data . '</script>';
    }
}

function getUserBalance($user_id)
{
    $wallets = Wallet::where(['user_id' => $user_id, 'coin_type' => 'Default']);

    $data['available_coin'] = $wallets->sum('balance');
    $data['available_used'] = $data['available_coin'] * settings('coin_price');
    //    $data['pending_withdrawal_coin'] = WithdrawHistory::whereIn('wallet_id',$wallets->pluck('id'))->where('status',STATUS_PENDING)->sum('amount');
    //    $data['pending_withdrawal_usd'] =  $data['pending_withdrawal_coin']*settings('coin_price');
    $coins = Coin::orderBy('id', 'ASC')->get();
    if (isset($coins[0])) {
        foreach ($coins as $coin) {
            $walletAmounts = Wallet::where(['user_id' => $user_id, 'coin_type' => $coin->type])->sum('balance');
            $data[$coin->type] = $walletAmounts;
        }
    }
    $data['pending_withdrawal_coin'] = 0;
    $data['pending_withdrawal_usd'] = 0;
    return $data;
}

// total withdrawal
function total_withdrawal($user_id)
{
    $total = 0;
    $withdrawal = WithdrawHistory::join('wallets', 'wallets.id', '=', 'withdraw_histories.wallet_id')
        ->where('wallets.user_id', $user_id)
        ->where('withdraw_histories.status', STATUS_SUCCESS)
        ->get();
    if (isset($withdrawal[0])) {
        $total = $withdrawal->sum('amount');
    }

    return $total;
}
// total deposit
function total_deposit($user_id)
{
    $total = 0;
    $deposit = DepositeTransaction::join('wallets', 'wallets.id', '=', 'deposite_transactions.receiver_wallet_id')
        ->where('wallets.user_id', $user_id)
        ->where('deposite_transactions.status', STATUS_SUCCESS)
        ->get();
    if (isset($deposit[0])) {
        $total = $deposit->sum('amount');
    }

    return $total;
}


function getActionHtmlForAdmin($user_id)
{
    $html = '<div class="activity-icon"><ul><li><a title="' . __('View') . '" href="' . route('viewAdminProfile', ['id' => encrypt($user_id)]) . '" class="user-two btn btn-info btn-sm"><span><i class="fa fa-eye"></i>' . __(' View') . '</span></a></li>
               <li><a title="' . __('Edit') . '" href="' . route('editAdminProfile', ['id' => encrypt($user_id)]) . '" class="user-two btn btn-primary btn-sm"><span><i class="fa fa-edit"></i>' . __(' Edit') . '</span></a></li>
               <li onclick="deleteAdminProfile(\'' . encrypt($user_id) . '\')"><a title="' . __('Delete') . '" href="#" class="user-two btn btn-danger btn-sm"><span><i class="fa fa-trash"></i>' . __(' Delete') . '</span></a></li></ul></div>';
    return $html;
}

function getActionHtmlForRole($id)
{
    $html = '<div class="activity-icon"><ul>
                <li><a title="' . __('Permission') . '" href="' . route('adminRoleList') . '?tab=role_list&id=' . encrypt($id) . '" class="user-two btn btn-primary btn-sm"><span><i class="fa fa-edit"></i>' . __(' Permission') . '</span></a></li>
               <li onclick="deleteRolePermission(\'' . encrypt($id) . '\')"><a title="' . __('Delete') . '" href="#" class="user-two btn btn-danger btn-sm"><span><i class="fa fa-trash"></i>' . __(' Delete') . '</span></a></li></ul></div>';
    return $html;
}

function getCheckboxForRole($id, $checked)
{
    $checked = $checked ? 'checked' : '';
    $html = '<input class="role_checkbox" ' . $checked . ' style="width: 30px; height: 30px;" type="checkbox" data-id="' . encrypt($id) . '" />';
    return $html;
}
//' . route('deleteAdminProfile',['id'=>encrypt($user_id)]) . '
function getActionHtml($list_type, $user_id, $item)
{

    $html = '<div class="activity-icon"><ul>';
    if ($list_type == 'active_users') {
        $html .= '
               <li><a title="' . __('Api Setting') . '" href="' . route('admin.user.api.access', encrypt($user_id)) . '" class="user-two btn btn-success btn-sm"><span><i class="fa fa-cog pr-1"></i>' . __(' Api Setting') . '</span></a></li>
               <li><a title="' . __('View') . '" href="' . route('adminUserProfile') . '?id=' . encrypt($user_id) . '&type=view" class="user-two btn btn-info btn-sm"><span><i class="fa fa-eye pr-1"></i>' . __(' View') . '</span></a></li>
               <li><a title="' . __('Edit') . '" href="' . route('admin.UserEdit') . '?id=' . encrypt($user_id) . '&type=edit" class="user-two btn btn-primary btn-sm"><span><i class="fa fa-edit pr-1"></i>' . __(' Edit') . '</span></a></li>
               ' . suspend_html('admin.user.suspend', encrypt($user_id));
        if (!empty($item->google2fa_secret)) {
            $html .= gauth_html('admin.user.remove.gauth', encrypt($user_id));
        }
        $html .= delete_html('admin.user.delete', encrypt($user_id));
    } elseif ($list_type == 'suspend_user') {
        $html .= '<li><a title="' . __('View') . '" href="' . route('admin.UserEdit') . '?id=' . encrypt($user_id) . '&type=view" class="btn btn-info btn-sm"><span><i class="fa fa-eye"></i>' . __(' View') . '</span></a></li>
          <li><a data-toggle="tooltip" title="Activate" href="' . route('admin.user.active', encrypt($user_id)) . '" class="btn btn-success btn-sm"><span><i class="fa fa-check-circle-o"></i>' . __(' Activate') . '</span></a></li>
         ';
    } elseif ($list_type == 'deleted_user') {
        $html .= '<li><a title="' . __('View') . '" href="' . route('admin.UserEdit') . '?id=' . encrypt($user_id) . '&type=view" class="btn btn-info btn-sm"><span><i class="fa fa-eye"></i>' . __(' View') . '</span></a></li>
          <li><a data-toggle="tooltip" title="Activate" href="' . route('admin.user.active', encrypt($user_id)) . '" class="btn btn-success btn-sm"><span><i class="fa fa-check-circle-o"></i>' . __(' Activate') . '</span></a></li>
          <li><a data-toggle="tooltip" title="Force Delete" href="' . route('adminUserForceDelete', encrypt($user_id)) . '" class="btn btn-danger btn-sm"><span><i class="fa fa-trash"></i>' . __(' Force Delete') . '</span></a></li>
         ';
    } elseif ($list_type == 'delete_request') {
        $html .= '<li><a title="' . __('View') . '" href="' . route('admin.UserEdit') . '?id=' . encrypt($user_id) . '&type=view" class="btn btn-info btn-sm"><span><i class="fa fa-eye"></i>' . __(' View') . '</span></a></li>';

        if ($item->delete_request == USER_DELETE_REQUEST_STATUS_PENDING) {
            $html .= '<li>' . userDeleteRequestAccepted(encrypt($user_id), $item) . '</li>
            <li><a data-toggle="tooltip" title="Force Delete" href="' . route('adminUserDeleteRequestRejected', encrypt($user_id)) . '" class="btn btn-danger btn-sm"><span><i class="fa fa-trash"></i>' . __('Rejected') . '</span></a></li>
            ';
        }
    } elseif ($list_type == 'email_pending') {
        $html .= ' <li><a data-toggle="tooltip" title="Email verify" href="' . route('admin.user.email.verify', encrypt($user_id)) . '" class="btn btn-success btn-sm"><span><i class="fa fa-envelope"></i>' . __(' Verify') . '</span></a></li>';
    } elseif ($list_type == 'phone_pending') {
        $html .= ' <li><a data-toggle="tooltip" title="Phone verify" href="' . route('admin.user.phone.verify', encrypt($user_id)) . '" class="btn btn-success btn-sm"><span><i class="fa fa-mobile"></i>' . __(' Verify') . '</span></a></li>';
    }
    $html .= '</ul></div>';
    return $html;
}

// Html render
/**
 * @param $route
 * @param $id
 * @return string
 */
function edit_html($route, $id)
{
    $html = '<li class="viewuser"><a title="' . __('Edit') . '" href="' . route($route, encrypt($id)) . '"><i class="fa fa-pencil"></i></a></li>';
    return $html;
}


/**
 * @param $route
 * @param $id
 * @return string
 * @throws Exception
 */

function receipt_view_html($image_link)
{
    $num = random_int(1111111111, 9999999999999);
    $html = '<div class="deleteuser"><a title="' . __('Bank receipt') . '" href="#view_' . $num . '" data-toggle="modal">Bank Deposit</a> </div>';
    $html .= '<div id="view_' . $num . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-lg">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Bank receipt') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><img src="' . $image_link . '" alt=""></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function delete_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('delete') . '" href="#delete_' . ($id) . '" data-toggle="modal" class="btn btn-danger btn-sm"><span><i class="fa fa-trash pr-1"></i>' . __(' Delete') . '</span></a> </li>';
    $html .= '<div id="delete_' . ($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to delete ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
function delete_html2($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('delete') . '" href="#delete_' . ($id) . '" data-toggle="modal"><span class="flaticon-delete-user"></span></a> </li>';
    $html .= '<div id="delete_' . ($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to delete ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function suspend_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('Suspend') . '" href="#suspends_' . decrypt($id) . '" data-toggle="modal" class="btn btn-warning btn-sm"><span><i class="fa fa-minus-circle pr-1"></i>' . __(' Suspend') . '</span></a> </li>';
    $html .= '<div id="suspends_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Suspend') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to suspend ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function active_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('Active') . '" href="#active_' . decrypt($id) . '" data-toggle="modal"><span class="flaticon-delete"></span></a> </li>';
    $html .= '<div id="active_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Active ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-success" href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function accept_html($route, $id)
{
    $html = '<li class="deleteuser m-0"><a title="' . __('Accept') . '" href="#accept_' . decrypt($id) . '" data-toggle="modal"><span class=""><i style="font-size: 22px;color: #718a71" class="fa fa-check-circle" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="accept_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Accept') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Accept ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-success" href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function default_accept_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('Accept') . '" href="#accept_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-check-circle-o" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="accept_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<form action="' . route($route) . '" method="get">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Accept') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">';
    $html .= '<p class="text-warning">' . __('Do you want to Accept ?') . '</p>';
    $html .= '<input type="hidden" name="withdrawal_id" value="' . $id . '">';
    $html .= '<label>' . __('Transaction Hash') . '</label>';
    $html .= '<input type="text" required name="transaction_hash" class="form-control">';
    $html .= '<small>' . __('It is a default coin withdrawal . so please manually send coin and put here the transaction hash ') . '</small>';
    $html .= '</div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<button type="submit" class="btn btn-success" >' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function reject_html($route, $id)
{
    $html = '<li class="deleteuser m-0"><a title="' . __('Reject') . '" href="#reject_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-times-circle" style="font-size: 22px;color: #ea98a0" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="reject_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Reject') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Reject ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger" href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function show_text_html($id, $text)
{
    $html = '<a href="#view_' . $id . '" data-toggle="modal">' . __('View') . '</a>';
    $html .= '<div id="view_' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Note') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . $text . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function reject_html_get_reject_note($route, $id)
{
    $html = '<li class="deleteuser m-0"><a title="' . __('Reject') . '" href="#reject_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-times-circle" style="font-size: 22px; color: #FF4433" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="reject_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Reject') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<form action="' . route($route) . '"method="post">';
    $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
    $html .= '<input type="hidden" name="id" value="' . $id . '" />';
    $html .= '<div class="modal-body">';
    $html .= '<p>' . __('Do you want to Reject ?') . '</p>';
    $html .= '<label>' . __('Rejected For') . '</label>';
    $html .= '<textarea name="reject_note" style="width:100%;height: 120px;" required></textarea>';
    $html .= '</div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<button class="btn btn-danger" type="submit">' . __('Confirm') . '</button>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function upload_image_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('Accept') . '" href="#image_upload_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-check-circle-o" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="image_upload_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Upload Your Receipt') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<form action="' . route($route) . '"method="post" enctype="multipart/form-data">';
    $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
    $html .= '<input type="hidden" name="id" value="' . $id . '" />';
    $html .= '<div class="modal-body">';
    $html .= '<label>' . __('Bank Receipt') . '</label>';
    $html .= '<input name="receipt" type="file" required />';
    $html .= '</div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<button class="btn btn-primary" type="submit">' . __('Accept') . '</button>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}


function html_form_send($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('Accept') . '" href="#htmlFormSending_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-check-circle-o" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="htmlFormSending_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Accept') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<form action="' . route($route) . '"method="post" enctype="multipart/form-data">';
    $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
    $html .= '<input type="hidden" name="id" value="' . $id . '" />';
    $html .= '<div class="modal-body">';
    $html .= '<label>' . __('Upload Bank Slip') . '</label>';
    $html .= '<input type="file" name="file" class="form-control-file" required />';
    $html .= '</div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<button class="btn btn-danger" type="submit">' . __('Confirm') . '</button>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}


function imageshowhtml($id, $image)
{
    $html = '<li style="list-style: none;" class="deleteuser"><a title="' . __('Image') . '" href="#show_image_html_' . $id . '" data-toggle="modal"><span class="">' . __('show') . '
    </span></a> </li>';
    $html .= '<div id="show_image_html_' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-lg">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Payment Slip') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">';
    $html .= '<div><img src="' . $image . '"  height="100%" width="100%" /></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
function bankShowHtml($bank)
{
    if (!$bank)
        return __('Bank not found');
    $html = '<li style="list-style: none;" class="userbank"><a title="' . __('Image') . '" href="#show_bank_html_' . $bank->id . '" data-toggle="modal"><span class="">' . $bank->bank_name . '
    </span></a> </li>';
    $html .= '<div id="show_bank_html_' . $bank->id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-lg">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('User Bank Details') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">';
    $html .=
        '<div>
            <p>
                ' . __("Bank name") . ' : ' . $bank->bank_name . '<br>
                ' . __("Bank holder name") . ' : ' . $bank->account_holder_name . '<br>
                ' . __("Bank address") . ' : ' . $bank->bank_address . '<br>
                ' . __("Bank address") . ' : ' . $bank->bank_address . '<br>
                ' . __("Bank IBAN") . ' : ' . $bank->iban . '<br>
                ' . __("Country") . ' : ' . $bank->country . '<br>
                ' . __("Bank swift") . ' : ' . $bank->swift_code . '
            </p>
    </div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function bankDepositShowHtml($item, $bank, $recipt)
{
    if (!$bank)
        return __('Bank not found');
    $html = '<li style="list-style: none;" class="userbank"><a title="' . __('Image') . '" href="#show_bank_html_' . $item->id . '" data-toggle="modal"><span class="">' . $bank->bank_name . '
    </span></a> </li>';
    $html .= '<div id="show_bank_html_' . $item->id . '" class="modal fade delete text-left" role="dialog">';
    $html .= '<div class="modal-dialog modal-lg">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('User Bank Details') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">';
    $html .=
        '<div>
            <a href="' . route('downloadCurrencyDeposit', ['id' => $item->id]) . ' class=""">
                <img src="' . asset(IMG_SLEEP_PATH . $recipt) . '" class="img-fluid" height="100%" alt="" />
            </a>
            <p>
                ' . __("Bank name") . ' : ' . $bank->bank_name . '<br>
                ' . __("Bank holder name") . ' : ' . $bank->account_holder_name . '<br>
                ' . __("Bank address") . ' : ' . $bank->bank_address . '<br>
                ' . __("Bank address") . ' : ' . $bank->bank_address . '<br>
                ' . __("Bank IBAN") . ' : ' . $bank->iban . '<br>
                ' . __("Country") . ' : ' . $bank->country . '<br>
                ' . __("Bank swift") . ' : ' . $bank->swift_code . '
            </p>
    </div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function bankWithdrawalShowHtml($item, $bank)
{
    if (!$bank)
        return __('Bank not found');
    $html = '<li style="list-style: none;" class="userbank"><a title="' . __('Image') . '" href="#show_bank_html_' . $item->id . '" data-toggle="modal"><span class="">' . $bank->bank_name . '
    </span></a> </li>';
    $html .= '<div id="show_bank_html_' . $item->id . '" class="modal fade delete text-left" role="dialog">';
    $html .= '<div class="modal-dialog modal-lg">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('User Bank Details') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">';
    $html .=
        '<div>
            <p>
                ' . __("Bank name") . ' : ' . $bank->bank_name . '<br>
                ' . __("Bank holder name") . ' : ' . $bank->account_holder_name . '<br>
                ' . __("Bank address") . ' : ' . $bank->bank_address . '<br>
                ' . __("Bank address") . ' : ' . $bank->bank_address . '<br>
                ' . __("Bank IBAN") . ' : ' . $bank->iban . '<br>
                ' . __("Country") . ' : ' . $bank->country . '<br>
                ' . __("Bank swift") . ' : ' . $bank->swift_code . '
            </p>
    </div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}


/**
 * @param $route
 * @param $id
 * @return string
 */
function ChangeStatus($route, $id)
{
    $html = '<li class=""><a href="#status_' . $id . '" data-toggle="modal"><i class="fa fa-ban"></i></a> </li>';
    $html .= '<div id="status_' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Block') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Block this product ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * @param $route
 * @param $id
 * @return string
 */
function BlockStatusChange($route, $id)
{
    $html = '<ul class="activity-menu">';
    $html .= '<li class=" "><a title="' . __('Status change') . '" href="#blockuser' . $id . '" data-toggle="modal"><i class="fa fa-check"></i></a> </li>';
    $html .= '<div id="blockuser' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Block') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Unblock this product ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-success"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</ul>';

    return $html;
}

/**
 * @param $route
 * @param $param
 * @return string
 */
function cancelSentItem($route, $param)
{
    $html = '<li class=""><a title="' . __('Cancel') . '" class="delete" href="#blockuser' . $param . '" data-toggle="modal"><i class="fa fa-remove"></i></a> </li>';
    $html .= '<div id="blockuser' . $param . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Cancel') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to cancel this product ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-success"href="' . route($route) . $param . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';


    return $html;
}

function modal_image_show($id, $image_name)
{
    $html = '<a title="' . __('Cancel') . '" class="delete" href="#image_id_' . $id . '" data-toggle="modal">' . __('Show Bank Receipt') . '</a>';
    $html .= '<div id="image_id_' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-md">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Cancel') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">
    <div style="display:block;">
    <img style="width: 100%;" src="' . asset('/uploaded_file/sleep') . '/' . $image_name . '" alt="Forest"></div></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
//status search
/**
 * @param $keyword
 * @return array
 */
function status_search($keyword)
{
    $st = [];
    if (strpos('_active', strtolower($keyword)) != false) {
        array_push($st, STATUS_SUCCESS);
    }
    if (strpos('_pending', strtolower($keyword)) != false) {
        array_push($st, STATUS_PENDING);
    }
    if (strpos('_inactive', strtolower($keyword)) != false) {
        array_push($st, STATUS_PENDING);
    }

    if (strpos('_deleted', strtolower($keyword)) != false) {
        array_push($st, STATUS_DELETED);
    }
    return $st;
}

function cim_search($keyword)
{

    return $keyword;
}

/**
 * @param $route
 * @param $status
 * @param $id
 * @return string
 */
function statusChange_html($route, $status, $id)
{
    $icon = ($status != STATUS_SUCCESS) ? '<i class="fa fa-check"></i>' : '<i class="fa fa-close"></i>';
    $status_title = ($status != STATUS_SUCCESS) ? statusAction(STATUS_SUCCESS) : statusAction(STATUS_SUSPENDED);
    $html = '';
    $html .= '<a title="' . __('Status change') . '" href="' . route($route, encrypt($id)) . '">' . $icon . '<span>' . $status_title . '</span></a> </li>';
    return $html;
}

//exists img search
/**
 * @param $image
 * @param $path
 * @return string
 */
function imageSrc($image, $path)
{

    $return = asset('admin/images/default.jpg');
    if (!empty($image) && file_exists(public_path($path . '/' . $image))) {
        $return = asset($path . '/' . $image);
    }
    return $return;
}
//exists img search
/**
 * @param $image
 * @param $path
 * @return string
 */
function imageSrcUser($image, $path)
{

    $return = asset('assets/img/avater.png');
    if (!empty($image) && file_exists(public_path($path . '/' . $image))) {
        $return = asset($path . '/' . $image);
    }
    return $return;
}

function imageSrcVerification($image, $path)
{


    $return = asset('/assets/images/default_card.svg');
    if (!empty($image) && file_exists(public_path($path . '/' . $image))) {
        $return = asset($path . '/' . $image);
    }
    return $return;
}

/**
 * @param $title
 */
function title($title)
{
    session(['title' => $title]);
}


/**
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function customNumberFormat($value)
{
    if (is_integer($value)) {
        return number_format($value, 8, '.', '');
    } elseif (is_string($value)) {
        $value = floatval($value);
    }
    $number = explode('.', number_format($value, 10, '.', ''));
    return $number[0] . '.' . substr($number[1], 0, 2);
}

if (!function_exists('max_level')) {
    function max_level()
    {
        $max_level = allsetting('max_affiliation_level');

        return $max_level ? $max_level : 3;
    }
}

if (!function_exists('user_balance')) {
    function user_balance($userId)
    {
        $balance = Wallet::where('user_id', $userId)->sum(DB::raw('balance + referral_balance'));

        return $balance ? $balance : 0;
    }
}

// Only for balance format
function truncate_num($value, $scale = 8): string
{
    if (is_integer($value)) {
        return number_format($value, 2, '.', '');
    } elseif (is_string($value)) {
        $value = floatval($value);
    }

    // Convert to string representation with the full precision
    $valueString = sprintf("%.10f", $value);

    // Split into integer and decimal parts
    $parts = explode('.', $valueString);

    // Keep only necessary decimal places without rounding
    $decimalPart = rtrim(substr($parts[1], 0, $scale), '0');

    // If the decimal part is empty, return only the integer part
    if (empty($decimalPart)) {
        return $parts[0];
    }

    // Combine the integer and formatted decimal parts
    return $parts[0] . '.' . $decimalPart;
}


function fmt_num($value, int $decimal = 8): string
{
    $value = is_string($value) ? floatval($value) : $value;
    $result = number_format($value, $decimal, '.', '');

    return (string) $result;
}

function trim_num($value, int $decimal = 8): string
{
    $number = fmt_num($value, $decimal);
    $result = rtrim(rtrim($number, '0'), '.');

    return (string) $result;
}

// comment author name
function comment_author_name($id)
{
    $name = '';
    $user = User::where('id', $id)->first();
    if (isset($user)) {
        $name = $user->first_name . ' ' . $user->last_name;
    }

    return $name;
}

function gauth_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('Reset gauth') . '" href="#remove_gauth_' . decrypt($id) . '" data-toggle="modal" class="btn btn-success btn-sm"><span><i class="fa fa-refresh"></i>' . __(' gAuth') . '</span></a> </li>';
    $html .= '<div id="remove_gauth_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Reset Gauth') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to remove gauth ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
if (!function_exists('all_months')) {
    function all_months($val = null)
    {
        $data = array(
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 11,
            12 => 12,
        );
        if ($val == null) {
            return $data;
        } else {
            return $data[$val];
        }
    }
}
function custom_number_format($value)
{
    if (is_integer($value)) {
        return number_format($value, 8, '.', '');
    } elseif (is_string($value)) {
        $value = floatval($value);
    }
    $number = explode('.', number_format($value, 10, '.', ''));
    return $number[0] . '.' . substr($number[1], 0, 8);
}

function converts_currency($amountInUsd, $to, $price)
{
    try {
        $array['amount'] = $amountInUsd;

        if ($to == "LTCT") {
            $to = "LTC";
        }

        if (($price['error'] == "ok")) {

            $one_coin = $price['result'][$to]['rate_btc']; // dynamic coin rate in btc

            $one_usd = $price['result']['USD']['rate_btc']; // 1 usd == btc rate

            $total_amount_in_usd = bcmulx($one_usd, $amountInUsd, 8);

            return custom_number_format(bcdivx($total_amount_in_usd, $one_coin, 8));
        }
    } catch (\Exception $e) {

        return number_format(0, 8);
    }
}


function convert_to_crypt($amountInBTC, $to)
{
    try {
        $coinpayment = new CoinPaymentsAPI();

        $price = $coinpayment->GetRates('');
        if (($price['error'] == "ok")) {

            $one_coin = $price['result'][$to]['rate_btc']; // dynamic coin rate in btc
            $one_usd = $price['result']['USD']['rate_btc']; // 1 usd ==  btc rate

            $total_amount_in_btc = bcmulx($one_coin, $amountInBTC, 8);
            $total_amount_in_usd = bcdivx($total_amount_in_btc, $one_usd, 8);

            return custom_number_format(bcdivx($total_amount_in_usd, settings('coin_price'), 8));
        }
    } catch (\Exception $e) {
        return custom_number_format($amountInBTC, 8);
    }
}


//User Activity
function createUserActivity($userId, $action = '', $ip = null, $device = null)
{
    if ($ip == null) {
        $current_ip = get_clientIp();
    } else {
        $current_ip = $ip;
    }
    if ($device == null) {
        $agent = new Agent();
        $deviceType = isset($agent) && $agent->isMobile() == true ? 'Mobile' : 'Web';
    } else {
        $deviceType = $device == 1 ? 'Mobile' : 'Web';
    }

    //        try{
    //            $location = GeoIP::getLocation($current_ip);
    //            $country = $location->country;
    //        }catch(\Exception $e){
    //            $country  = '';
    //        }

    $activity['user_id'] = $userId;
    $activity['action'] = $action;
    $activity['ip_address'] = isset($current_ip) ? $current_ip : '0.0.0.0';
    $activity['source'] = $deviceType;
    $activity['location'] = '';
    ActivityLog::create($activity);
}
// user image
function show_image($id, $type)
{
    $img = asset('assets/img/avater.png');
    if ($type == 'logo') {
        if (!empty(allsetting('logo'))) {
            $img = asset(path_image() . allsetting('logo'));
        } else {
            $img = asset('assets/user/images/logo.svg');
        }
    } elseif ($type == 'login_logo') {
        if (!empty(allsetting('login_logo'))) {
            $img = asset(path_image() . allsetting('login_logo'));
        } else {
            $img = asset('assets/user/images/logo.svg');
        }
    } else {
        if (!empty($id)) {
            $user = User::where('id', $id)->first();
            if (isset($user) && !empty($user->photo)) {
                $img = asset(IMG_USER_PATH . $user->photo);
            }
        }
    }
    return $img;
}
// plan image
function show_plan_image($plan_id, $img = null)
{
    $image = asset('assets/img/badge/Gold.svg');
    if (!empty($img)) {
        $image = asset(path_image() . $img);
    } else {
        if ($plan_id == 1) {
            $image = asset('assets/img/badge/Silver.svg');
        } elseif ($plan_id == 2) {
            $image = asset('assets/img/badge/Gold.svg');
        } elseif ($plan_id == 3) {
            $image = asset('assets/img/badge/Platinum.svg');
        }
    }

    return $image;
}

// member plan bonus percentage
function plan_bonus_percentage($type, $bonus, $amount)
{
    $bonus_percentage = $bonus;
    if ($type == PLAN_BONUS_TYPE_FIXED) {
        $bonus_percentage = (100 * $bonus) / $amount;
    }

    return number_format($bonus_percentage, 2);
}
// calculate bonus
function calculate_plan_bonus($bonus_percentage, $amount)
{
    $bonus = ($bonus_percentage * $amount) / 100;

    return number_format($bonus, 8);
}

// get coin payment address
function get_coin_payment_address($payment_type)
{
    storeBotException('get_coin_payment_address ', 'start the process ');
    try {
        $coin_payment = new CoinPaymentsAPI();
        $ipnUrl = url('api/coin-payment-notifier');
        $address = $coin_payment->GetCallbackAddress($payment_type, $ipnUrl);
        storeBotException('address : ', json_encode($address));
        if (isset($address['error']) && ($address['error'] == 'ok')) {
            $data['memo'] = "";
            $data['address'] = $address['result']['address'];
            if (isset($address['result']['dest_tag'])) {
                $data['memo'] = $address['result']['dest_tag'];
            }
            return $data;
        } else {
            storeException('get_coin_payment_address ', ' address not generated');
            return false;
        }
    } catch (\Exception $e) {
        storeException('get_coin_payment_address exception ', $e->getMessage());
    }
    return false;
}
// get node address
function get_node_address($coin_type)
{
    try {
        $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
            ->where(['coins.coin_type' => $coin_type])
            ->select('coins.*', 'coin_settings.*')
            ->first();
        if ($coin) {
            $bitCoinApi = new BitCoinApiService($coin->coin_api_user, decryptId($coin->coin_api_pass), $coin->coin_api_host, $coin->coin_api_port);
            $address = $bitCoinApi->getNewAddress();
            if (isset($address)) {
                return $address;
            } else {
                storeException('get_node_address BitCoinApi ', ' address not generated');
                return false;
            }
        } else {
            storeBotException('get_node_address ', ' Coin not found');
            return false;
        }
    } catch (\Exception $e) {
        storeException('get_node_address exception ', $e->getMessage());
    }
}


// get bitgo address
function get_bitgo_address($coin_type)
{
    try {
        $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
            ->where(['coins.coin_type' => $coin_type])
            ->first();
        if ($coin) {
            if (empty($coin->bitgo_wallet_id)) {
                storeException('get_bitgo_address ', 'bitgo_wallet_id not found');
                return false;
            } else {
                $bitgoApi = new BitgoWalletService();
                $address = $bitgoApi->createBitgoWalletAddress($coin->coin_type, $coin->bitgo_wallet_id, $coin->chain);
                storeBotException('address bitgo', json_encode($address));
                if ($address['success']) {
                    return $address['data']['address'];
                } else {
                    storeException('get_bitgo_address address', $address['message']);
                    return false;
                }
            }
        } else {
            storeBotException('get_bitgo_address ', ' Coin not found');
            return false;
        }
    } catch (\Exception $e) {
        storeException('get_bitgo_address ', $e->getMessage());
    }
}

// get erc20 or bep20 address
function get_erc20_address($coin_type)
{
    storeBotException('get_erc20_address', 'start the process ' . $coin_type);
    try {
        $coin = Coin::join('coin_settings', 'coin_settings.coin_id', '=', 'coins.id')
            ->where(['coins.coin_type' => $coin_type])
            ->first();

        if ($coin) {
            if (empty($coin->chain_link)) {
                storeBotException('get_erc20_address ', 'chain link not found');
                return false;
            } else {
                $api = new ERC20TokenApi($coin);
                $address = $api->createNewWallet();
                if ($address['success']) {
                    return $address['data'];
                } else {
                    storeException('get_erc20_address address', $address['message']);
                    return false;
                }
            }
        } else {
            storeBotException('get_erc20_address ', ' Coin not found');
            return false;
        }
    } catch (\Exception $e) {
        storeException('get_erc20_address ', $e->getMessage());
    }
}

// get coin address
function get_coin_address($coin_type, $network)
{
    $address = '';
    $data = [
        'wallet_key' => '',
        'public_key' => '',
        'memo' => ''
    ];
    try {
        if ($network == COIN_PAYMENT) {
            $addressInfo = get_coin_payment_address($coin_type);
            if ($addressInfo) {
                $address = $addressInfo['address'];
                $data['memo'] = $addressInfo['memo'];
            }
        } elseif ($network == BITCOIN_API) {
            $address = get_node_address($coin_type);
        } elseif ($network == BITGO_API) {
            $address = get_bitgo_address($coin_type);
        } elseif ($network == ERC20_TOKEN || $network == BEP20_TOKEN || $network == TRC20_TOKEN || $network == MATIC_TOKEN) {
            $result = get_erc20_address($coin_type);
            if ($result) {
                $address = $result->address;
                $data['wallet_key'] = $result->privateKey;
                $data['public_key'] = $result->publicKey ?? '';
            }
        }
        $data['address'] = $address;
    } catch (\Exception $e) {
        storeException('get_coin_address', $e->getMessage());
    }
    return $data;
}


// check primary wallet
function is_primary_wallet($wallet_id, $coin_type)
{
    $wallets = Wallet::where(['user_id' => Auth::id(), 'coin_type' => $coin_type, 'is_primary' => 1])->get();
    $this_primary_id = 0;
    $primary = 0;
    if (isset($wallets[0])) {
        foreach ($wallets as $wallet) {
            if ($wallet->id == $wallet_id) {
                $this_primary_id = $wallet->id;
            }
        }
    }
    if ($this_primary_id == $wallet_id) {
        $primary = 1;
    }

    return $primary;
}

// check coin type
function check_coin_type($type)
{
    $coin = Coin::where('type', $type)->first();
    if (isset($coin)) {
        return $coin->type;
    }

    return 'BTC';
}

// find primary wallet
function get_primary_wallet($user_id, $coin_type)
{
    $primaryWallet = Wallet::where(['user_id' => $user_id, 'coin_type' => $coin_type])->first();
    if (isset($primaryWallet)) {
        return $primaryWallet;
    } else {
        $createWallet = Wallet::create(['user_id' => $user_id, 'name' => $coin_type . ' Wallet', 'coin_type' => $coin_type, 'is_primary' => 1]);
        return $createWallet;
    }
}

if (!function_exists('co_wallet_feature_active')) {
    function co_wallet_feature_active()
    {
        $coWalletFeatureActive = settings(CO_WALLET_FEATURE_ACTIVE_SLUG);
        if ($coWalletFeatureActive == STATUS_ACTIVE)
            return true;
        else
            return false;
    }
}

function find_coin_type($coin_type)
{
    $type = $coin_type;
    if ($coin_type == 'Default') {
        $type = settings('coin_name');
    }

    return $type;
}

function getUserId()
{
    try {
        return auth('api')->user()->id;
    } catch (\Exception $e) {
        return 0;
    }
}

function show_image_path($img_name, $path)
{
    $img = asset('assets/img/placeholder-image.png');
    if (!empty($img_name)) {
        $img = asset(path_image() . $path . $img_name);
    }

    return $img;
}

function isFeesZero($id, $baseCoin, $tradeCoin, $amount, $orderType, $price = null)
{
    if ($price == null) {
        if ($orderType == 'buy') {
            $repo = new SellOrderRepository(Sell::class);
            $price = $repo->getSellMarketPrice($baseCoin, $tradeCoin, $amount);
        } else {
            $repo = new BuyOrderRepository(Buy::class);
            $price = $repo->getBuyMarketPrice($baseCoin, $tradeCoin, $amount);
        }

        if (calcualte_fee_for_user($id) != 0 && bcmulx(bcmulx($price, $amount), bcdivx(calcualte_fee_for_user($id), '100')) == 0) {
            return bcdivx('0.00000001', bcmulx(bcdivx(calcualte_fee_for_user($id), '100'), $price));
        } else {
            return 0;
        }
    } else {
        if (calcualte_fee_for_user($id) != 0 && bcmulx(bcmulx($price, $amount), bcdivx(calcualte_fee_for_user($id), '100')) == 0) {
            return bcmulx(bcdivx('0.00000001', calcualte_fee_for_user($id)), '100');
        } else {
            return 0;
        }
    }
}

function isFeesZeroForMarket($id, $amount)
{
    $fees = calcualte_fee_for_user($id);
    if ($fees != 0 && bcmulx($amount, bcdivx($fees, '100')) == 0) {
        return bcmulx(bcdivx('0.00000001', $fees), '100');
    } else {
        return 0;
    }
}

function calcualte_fee_for_user($id)
{
    $fees = calculated_fee_limit($id);
    return ($fees['maker_fees'] > $fees['taker_fees'] ? $fees['maker_fees'] : $fees['taker_fees']);
}

function calculated_fee_limit($userId)
{
    $query = DB::select("select sum(btc) as total FROM transactions WHERE (buy_user_id = $userId or sell_user_id = $userId) AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $tradeVolume = $query[0]->total;
    $fees = [
        'maker_fees' => 0,
        'taker_fees' => 0,
        'thirtyDayVolume' => $tradeVolume,
    ];

    $limits = AdminSetting::where('slug', 'like', 'trade_limit_%')->get();

    $slugs = [];
    foreach ($limits as $limit) {
        if (bccompx($tradeVolume, $limit->value) !== -1) {
            $slugs[] = 'maker_' . explode('_', $limit->slug)[2];
            $slugs[] = 'taker_' . explode('_', $limit->slug)[2];
            $adminSetting = allsetting($slugs);
            $fees['maker_fees'] = $adminSetting['maker_' . explode('_', $limit->slug)[2]];
            $fees['taker_fees'] = $adminSetting['taker_' . explode('_', $limit->slug)[2]];
        }
    }

    return $fees;
}

function getBtcRate($tradeCoinId)
{
    $btcCoin = Coin::where(['coin_type' => 'BTC'])->first();
    try {
        $dashboardService = new DashboardService();
        $response = $dashboardService->getLastPriceList($btcCoin->id, $tradeCoinId)->first();
        return $response->price;
    } catch (\Exception $e) {
        return 0;
    }
}



function customEncrypt($string)
{
    $key = env('APP_KEY');
    $result = '';
    for ($i = 0, $k = strlen($string); $i < $k; $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) + ord($keychar));
        $result .= $char;
    }
    return base64_encode($result);
}

function customDecrypt($string)
{
    $key = env('APP_KEY');
    $result = '';
    $string = base64_decode($string);
    for ($i = 0, $k = strlen($string); $i < $k; $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key)) - 1, 1);
        $char = chr(ord($char) - ord($keychar));
        $result .= $char;
    }
    return $result;
}


function getError($e)
{
    if (env('APP_DEBUG')) {
        return " => " . $e->getMessage();
    }
    return '';
}

function arrValueOnly($array, $seperator = ',')
{
    $string = '';
    $sep = '';
    foreach ($array as $key => $val) {
        $string .= $sep . $val;
        $sep = $seperator;
    }
    return $string;
}

function coin_type_restrict_trade($a = null)
{
    $coinService = new CoinService();
    $coinType = $coinService->getCoin(['status' => 1, 'trade_status' => 1])->toArray();
    $a = strtoupper($a);
    if ($a == null) {
        return $coinType;
    } else {
        return isset($coinType[$a]) ? $coinType[$a] : [];
    }
}

function bscointype($coin = null)
{
    $coinService = new CoinService();
    $allCoins = $coinService->getCoin(['status' => 1, 'trade_status' => 1]);
    $baseCoin = array();
    foreach ($allCoins as $coin) {
        if ($coin['is_base'] == '1') {
            array_push($baseCoin, $coin['id']);
        }
    }

    return $baseCoin;
}

function getService($data = [], $appName = null)
{
    try {
        $coinService = new CoinService();
        $response = $coinService->{$data['method']}($data['params']);
        return $response->toArray();
    } catch (\Exception $exception) {
        return false;
    }
}

function getImageUrl($filePath)
{
    return url(Storage::url($filePath));
}

function fixedlenstr($data, $length = 20, $padString = "0", $position = 'left')
{
    if ($position === 'left') {
        $position = STR_PAD_LEFT;
    } elseif ($position === 'right') {
        $position = STR_PAD_RIGHT;
    } else {
        $position = STR_PAD_BOTH;
    }
    return str_pad($data, $length, $padString, $position);
}



function decryptId($encryptedId)
{
    try {
        $id = decrypt($encryptedId);
    } catch (Exception $e) {
        storeException('decryptId', $e->getMessage());
        return ['success' => false];
    }
    return $id;
}

/**
 * Prepare OrderPlace, OrderRemove data for broadcasting and call broadcastData function for broadcast
 * @param $order Object
 * @param $orderType string buy/sell
 * @param $event string
 * @param $userId integer|null
 * @return void
 */
function broadcastOrderData($order, $orderType, $event, $userId = null)
{
    // $t = time();

    // if ($userId == null) {
    //     $userId = Auth::id();
    // }
    // if ($orderType == 'buy') {
    //     if (true || (env('APP_ENV') == 'local') || (env('APP_ENV') == 'dev')) {
    //         $data = DB::table(DB::raw('(select visualNumberFormat(TRUNCATE(price,8)) as price, visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount, visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total
    //                                     from buys
    //                                     where base_coin_id = ' . $order->base_coin_id . ' and trade_coin_id = ' . $order->trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL and price = ' . $order->price . '
    //                                     group by price
    //                                     order by price desc )
    //                                  t1'))
    //             ->leftJoin(DB::raw('(select visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount ,price from buys where user_id =' . $order->user_id . ' and base_coin_id = ' . $order->base_coin_id . ' and trade_coin_id = ' . $order->trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL and price = ' . $order->price . ' group by price order by price desc) t2'), ['t1.price' => 't2.price'])
    //             ->select('t1.amount', 't2.amount as my_size')->first();

    //         $myOrder = Buy::select(DB::raw('visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount'))->where(['user_id' => $userId, 'price' => $order->price, 'base_coin_id' => $order->base_coin_id, 'trade_coin_id' => $order->trade_coin_id, 'status' => 0, 'is_market' => 0])->first();

    //     }
    // } else {
    //     if (true || (env('APP_ENV') == 'local') || (env('APP_ENV') == 'dev')) {
    //         $data = DB::table(DB::raw('(select visualNumberFormat(TRUNCATE(price,8)) as price, visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount, visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total
    //                                     from sells
    //                                     where base_coin_id = ' . $order->base_coin_id . ' and trade_coin_id = ' . $order->trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL and price = ' . $order->price . '
    //                                     group by price
    //                                     order by price desc )
    //                                  t1'))
    //             ->leftJoin(DB::raw('(select visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount ,price from sells where user_id =' . $order->user_id . ' and base_coin_id = ' . $order->base_coin_id . ' and trade_coin_id = ' . $order->trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL and price = ' . $order->price . ' group by price order by price desc) t2'), ['t1.price' => 't2.price'])
    //             ->select('t1.amount', 't2.amount as my_size')->first();
    //         $myOrder = Sell::select(DB::raw('visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount'))->where(['user_id' => $userId, 'price' => $order->price, 'base_coin_id' => $order->base_coin_id, 'trade_coin_id' => $order->trade_coin_id, 'status' => 0, 'is_market' => 0])->first();


    //     }
    // }

    // $amount = empty($data) ? 0 : $data->amount;
    // $mySize = empty($data) ? 0 : $data->my_size;
    // $globalData = [
    //     'orderType' => $orderType,
    //     'price' => $order->price,
    //     'amount' => $amount,
    //     'my_size' => $mySize,
    //     'total' => bcmulx($order->price, $amount),
    //     'base_coin_id' => $order->base_coin_id,
    //     'trade_coin_id' => $order->trade_coin_id,
    // ];
    // broadcastPublic( $event, $globalData);

    // $userFees = $order->maker_fees > $order->taker_fees ? $order->maker_fees : $order->taker_fees;
    // $fees = bcmulx(bcmulx(bcmulx($order->price, bcsubx($order->amount, $order->processed)), $userFees), '0.01');
    // $personalData = [
    //     'orderType' => $orderType,
    //     'price' => $order->price,
    //     'my_size' => $myOrder->amount,
    //     'amount' => bcsubx($order->amount, $order->processed),
    //     'total' => bcmulx($order->price, bcsubx($order->amount, $order->processed)),
    //     'base_coin_id' => $order->base_coin_id,
    //     'trade_coin_id' => $order->trade_coin_id,
    //     'id' => $order->id,
    //     'fees' => $fees,
    //     'created_at' => $order->created_at->toDateTimeString()
    // ];

    // broadcastPrivate( $event, $personalData, $order->user_id);
}


/**
 * Api Request for Broadcasting.
 * @param $channelName string
 * @param $eventName string
 * @param $broadcastData array
 * @param null $userId integer
 * @return void
 */
function broadcastPublic($eventName, $broadcastData)
{
    $chanel = env("PUSHER_PUBLIC_CHANEL_NAME") ?? 'tradexpro_public_chanel';
    //    dispatch(new BroadcastJob($chanel, $eventName, $broadcastData))->onQueue('broadcast-data');
}

/**
 * Api Request for Broadcasting.
 * @param $channelName string
 * @param $eventName string
 * @param $broadcastData array
 * @param null $userId integer
 * @return void
 */
function broadcastPrivate($eventName, $broadcastData, $userId)
{
    $ch = env("PUSHER_PRIVATE_CHANEL_NAME") ?? 'tradexpro_private_chanel';
    $channelName = 'private-' . $ch . '.' . ($userId);
    //    dispatch(new BroadcastJob($channelName, $eventName, $broadcastData))->onQueue('broadcast-data');
}



/**
 * Broadcast Wallet data after balance Update
 * @param $walletId integer
 * @return void
 */
function broadcastWalletData($walletId, $userId = null)
{

    $walletRepo = new UserWalletRepository(UserWallet::class);
    $wallet = $walletRepo->getById($walletId);
    $repo = new DashboardRepository();
    if ($userId != null) {
        $onOrder = $repo->getOnOrderBalance($wallet->coin_id, $userId);
    } else {
        $onOrder = $repo->getOnOrderBalance($wallet->coin_id);
    }
    $data = [
        'coin_id' => $wallet->coin_id,
        'balance' => $wallet->balance,
        'on_order' => $onOrder
    ];
    broadcastPrivate('updateWallet', $data, $wallet->user_id);
}


function check_coin_status($coin, $userId, $amount, $fees = 0)
{
    $data = [
        'success' => true,
        'message' => 'ok',
        'data' => []
    ];
    if (isset($coin)) {
        if ($coin->coin_status != STATUS_ACTIVE) {
            $data = [
                'success' => false,
                'message' => $coin->coin_type . __(" coin is inactive right now."),
                'data' => []
            ];
            return $data;
        }
        if ($coin->is_withdrawal != STATUS_ACTIVE) {
            $data = [
                'success' => false,
                'message' => $coin->coin_type . __(" coin is not available for withdrawal right now"),
                'data' => []
            ];
            return $data;
        }
        if (($amount + $fees) < $coin->minimum_withdrawal) {
            $data = [
                'success' => false,
                'message' => __('Minimum withdrawal amount ') . $coin->minimum_withdrawal . ' ' . $coin->coin_type,
                'data' => []
            ];
            return $data;
        }
        if (($amount + $fees) > $coin->maximum_withdrawal) {
            $data = [
                'success' => false,
                'message' => __('Maximum withdrawal amount ') . $coin->maximum_withdrawal . ' ' . $coin->coin_type,
                'data' => []
            ];
            return $data;
        }

        $walletBalance = $coin->balance;
        if ($walletBalance < ($amount + $fees)) {
            $data = [
                'success' => false,
                'message' => __("Wallet has no enough balance to withdrawal"),
                'data' => []
            ];
            return $data;
        }
    }

    return $data;
}

function check_withdrawal_fees($amount, $fess_percentage, $type)
{
    return

        $type == DISCOUNT_TYPE_FIXED ? $fess_percentage : bcdivx(bcmulx($fess_percentage, $amount, 8), 100, 8);
}


function get_coin_type($coin_id)
{
    $type = '';
    $coin = Coin::find($coin_id);
    if (isset($coin)) {
        $type = $coin->coin_type;
    }

    return $type;
}

/**
 * broadcast all data like balance update, order delete, Trade History Update after transaction
 * @param $transaction object
 * @return void
 *
 */
function broadcastTransactionData($transaction)
{
    $transaction = Transaction::select('base_coin_id', 'trade_coin_id', 'buy_user_id', 'sell_user_id', DB::raw("visualNumberFormat(amount) as amount"), DB::raw("visualNumberFormat(price) as price"), DB::raw("visualNumberFormat(last_price) as last_price"), DB::raw("visualNumberFormat(total) as total"), 'price_order_type', 'created_at', DB::raw("TIME(created_at) as time"), 'buy_fees', 'sell_fees', 'buy_user_id', 'sell_user_id')->where('id', $transaction->id)->first();

    $coinPairs = CoinPair::select('parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as price"), DB::raw("visualNumberFormat(volume) as volume"), DB::raw("`change`"), DB::raw("visualNumberFormat(high) as high"), DB::raw("visualNumberFormat(low) as low"))
        ->where(['parent_coin_id' => $transaction->base_coin_id, 'child_coin_id' => $transaction->trade_coin_id])->first()->toArray();

    $globalData = [
        'price' => $transaction->price,
        'price_order_type' => $transaction->price_order_type,
        'last_price' => $transaction->last_price,
        'amount' => $transaction->amount,
        'total' => $transaction->total,
        'unix_time' => strtotime($transaction->created_at),
        'time' => $transaction->time,
        'created_at' => $transaction->created_at,
        'base_coin_id' => $transaction->base_coin_id,
        'trade_coin_id' => $transaction->trade_coin_id,
        'buy_user_id' => $transaction->buy_user_id,
        'sell_user_id' => $transaction->sell_user_id,
        'buy_fees' => $transaction->buy_fees,
        'sell_fees' => $transaction->sell_fees
    ];

    broadcastPublic('transaction', $globalData);
    broadcastPublic('twentyFourHoursChange', $coinPairs);
    broadcastPrivate('transaction', $globalData, $transaction->buy_user_id);
    if ($transaction->buy_user_id != $transaction->sell_user_id) {
        broadcastPrivate('transaction', $globalData, $transaction->sell_user_id);
    }
}

function get_coin_id($coin_type)
{
    $id = 0;
    $coin = Coin::where('coin_type', $coin_type)->first();
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}
function get_coin_id_test($coin_type)
{
    $id = 0;
    $coin = Coin::where('coin_type', $coin_type)->first();
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}
function get_default_base_coin_id($coin_type = null)
{
    $id = 2;
    if (isset($coin_type)) {
        $coin = Coin::where(['coin_type' => $coin_type])->first();
    } else {
        $pair = CoinPair::where(['status' => STATUS_ACTIVE])->first();
        if ($pair) {
            $coin = Coin::where(['id' => $pair->parent_coin_id])->first();
        }
    }
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}
function get_default_trade_coin_id($coin_type = null)
{
    $id = 1;
    if (isset($coin_type)) {
        $coin = Coin::where(['coin_type' => $coin_type])->first();
    } else {
        $pair = CoinPair::where(['status' => STATUS_ACTIVE])->first();
        if ($pair) {
            $coin = Coin::where(['id' => $pair->child_coin_id])->first();
        }
    }
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}


function make_unique_slug($title, $table_name = NULL, $column_name = 'slug')
{
    $table = array(
        'Š' => 'S',
        'š' => 's',
        'Đ' => 'Dj',
        'đ' => 'dj',
        'Ž' => 'Z',
        'ž' => 'z',
        'Č' => 'C',
        'č' => 'c',
        'Ć' => 'C',
        'ć' => 'c',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Å' => 'A',
        'Æ' => 'A',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'Ø' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
        'Þ' => 'B',
        'ß' => 'Ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'å' => 'a',
        'æ' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'o',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ý' => 'y',
        'ý' => 'y',
        'þ' => 'b',
        'ÿ' => 'y',
        'Ŕ' => 'R',
        'ŕ' => 'r',
        '/' => '-',
        ' ' => '-'
    );

    // -- Remove duplicated spaces
    $stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $title);

    // -- Returns the slug
    $slug = strtolower(strtr($title, $table));
    $slug = str_replace("?", "", $slug);
    if (isset($table_name)) {
        $item = DB::table($table_name)->where($column_name, $slug)->first();
        if (isset($item)) {
            $slug = setSlugAttribute($slug, $table_name, $column_name);
        }
    }

    return $slug;
}

function setSlugAttribute($value, $table, $column_name = 'slug')
{
    if (DB::table($table)->where($column_name, $value)->exists()) {
        return incrementSlug($value, $table, $column_name);
    }
    return $value;
}

function incrementSlug($slug, $table, $column_name = 'slug')
{
    $original = $slug;
    $count = 2;

    while (DB::table($table)->where($column_name, $slug)->exists()) {
        $slug = "{$original}-" . $count++;
    }

    return $slug;
}

function create_coin_wallet($user_id)
{
    $items = getMissingCoinWallet($user_id);
    if (!empty($items)) {
        foreach ($items as $item) {
            storeNewWallet($item);
        }
    }
}

function storeNewWallet($item)
{
    $checkWallet = Wallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id'], 'coin_type' => $item['coin_type']])->first();
    if (isset($checkWallet)) {
    } else {
        $checkWalletAgain = Wallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id']])->first();
        if (empty($checkWalletAgain)) {
            $againCheck = Wallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id']])->first();
            if (empty($againCheck)) {
                $a = Wallet::firstOrCreate([
                    'user_id' => $item['user_id'],
                    'coin_id' => $item['coin_id']
                ], [
                    'name' => $item['coin_type'] . ' wallet',
                    'coin_type' => $item['coin_type']
                ]);
            }
        }
    }
}

function getMissingCoinWallet($user_id)
{
    $coins = Coin::where(['status' => STATUS_ACTIVE])->get();
    $data = [];
    if (isset($coins[0])) {
        foreach ($coins as $coin) {
            $exist = Wallet::where(['user_id' => $user_id, 'coin_id' => $coin->id])->first();
            if (isset($exist)) {
            } else {
                $data[] = [
                    'coin_id' => $coin->id,
                    'coin_type' => $coin->coin_type,
                    'user_id' => $user_id,
                    'name' => $coin->coin_type . ' wallet',
                ];
            }
        }
    }
    return $data;
}

function get_wallet_balance_all($user_id, $coin_type = null)
{
    $data['balance'] = 0;
    $data['balance_usd'] = 0;
    if (isset($coin_type) && (!empty($coin_type))) {
        $data['balance'] = Wallet::where(['user_id' => $user_id, 'coin_type' => $coin_type])->sum('balance');
        if ($coin_type == DEFAULT_COIN_TYPE) {
            $data['balance_usd'] = bcmulx(settings('coin_price'), $data['balance'], 8);
        } else {
            $url = file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=' . $coin_type . '&tsyms=USD');
            $data['balance_usd'] = bcmulx($data['balance'], json_decode($url, true)['USD'], 8);
        }
    } else {
        $data['balance'] = Wallet::where(['user_id' => $user_id])->sum('balance');
    }
    return $data;
}
function get_coin_icon($icon)
{
    if (!empty($icon)) {
        return assert(IMG_ICON_PATH . '/' . $icon);
    } else {
        return asset('assets/user/images/bitcoin.png');
    }
}

function get_coin_usd_value($amount, $coin_type)
{
    return convert_currency($amount, getUserCurrency(), $coin_type, getUserCurrency());
}

function checkUserKyc($userId, $type, $verificationType)
{
    $response = ['success' => true, 'message' => 'success'];
    if ($type == KYC_DRIVING_REQUIRED) {
        $drive_front = VerificationDetails::where('user_id', $userId)->where('field_name', 'drive_front')->first();
        $drive_back = VerificationDetails::where('user_id', $userId)->where('field_name', 'drive_back')->first();
        if ((isset($drive_front) && isset($drive_back)) && (($drive_front->status == STATUS_SUCCESS) && ($drive_back->status == STATUS_SUCCESS))) {
            $response = ['success' => true, 'message' => 'success'];
        } else {
            $response = ['success' => false, 'message' => __('Before ') . $verificationType . __(' you must have verified driving licence')];
        }
        return $response;
    } elseif ($type == KYC_PASSPORT_REQUIRED) {
        $pass_front = VerificationDetails::where('user_id', $userId)->where('field_name', 'pass_front')->first();
        $pass_back = VerificationDetails::where('user_id', $userId)->where('field_name', 'pass_back')->first();
        if ((isset($pass_front) && isset($pass_back)) && (($pass_front->status == STATUS_SUCCESS) && ($pass_back->status == STATUS_SUCCESS))) {
            $response = ['success' => true, 'message' => 'success'];
        } else {
            $response = ['success' => false, 'message' => __('Before ') . $verificationType . __(' you must have verified passport')];
        }
        return $response;
    } else {
        $nid_front = VerificationDetails::where('user_id', $userId)->where('field_name', 'nid_front')->first();
        $nid_back = VerificationDetails::where('user_id', $userId)->where('field_name', 'nid_back')->first();
        if ((isset($nid_front) && isset($nid_back)) && (($nid_front->status == STATUS_SUCCESS) && ($nid_back->status == STATUS_SUCCESS))) {
            $response = ['success' => true, 'message' => 'success'];
        } else {
            $response = ['success' => false, 'message' => __('Before ') . $verificationType . __(' you must have verified NID')];
        }
        return $response;
    }
}

function sendDataThroughWebSocket($channel_name, $event_name, $data)
{
    $config = config('broadcasting.connections.pusher');
    $pusher = new Pusher($config['key'], $config['secret'], $config['app_id'], $config['options']);
    $pusher->trigger($channel_name, $event_name, $data);
}

function responseData(bool $status, string $message = '', $data = null)
{
    $message = !empty($message) ? $message : __('Something went wrong');
    return ['success' => $status, 'message' => $message, 'data' => $data];
}

/**
 * @param string $type Log type: DEBUG,WARN,ERROR etc...
 */
function storeException($type, $message, $stack_idx = 1)
{
    $logger = new Logger(null, null, $stack_idx);
    $logger->log(type: $type, msg: $message);
}

/**
 * @param string $level Log level: INFO,WARN,ERROR etc...
 */
function storeLog(mixed $message, string $level = 'info')
{
    $logger = new Logger(null, null, 1);
    $logger->log(type: $level, msg: $message);
}

// get wallet personal address
function get_wallet_personal_add($add1, $add2)
{
    $ex = STRONG_KEY . $add1;
    $data = explode($ex, $add2);
    return $data[1];
}


function getUserCurrencyApi()
{
    $data['base_currency'] = 'USD';
    $data['currency'] = 'USD';
    $data['currency_symbol'] = '$';
    $data['currency_rate'] = 1;
    if (Auth::guard('api')->check()) {
        $currency = CurrencyList::where(['code' => auth('api')->user()->currency])->first();
        if ($currency) {
            $data['currency'] = $currency->code;
            $data['currency_symbol'] = $currency->symbol;
            $data['currency_rate'] = $currency->rate;
        }
    }
    return $data;
}

function checkWalletBalanceByCoin($coinId)
{
    $data = Wallet::where(['coin_id' => $coinId])->sum('balance');
    return floatval($data) > 0 ? 1 : 0;
}
function checkWalletAddressByCoin($coinType)
{
    return WalletAddressHistory::where(['coin_type' => $coinType])->count();
}
function checkPairByCoin($coinId)
{
    $item = CoinPair::where(['parent_coin_id' => $coinId])->orWhere(['child_coin_id' => $coinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkDepositByCoin($coinType)
{
    $item = DepositeTransaction::where(['coin_type' => $coinType])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkWithdrawalByCoin($coinType)
{
    $item = WithdrawHistory::where(['coin_type' => $coinType])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkBuyByCoin($baseCoinId, $tradeCoinId)
{
    $item = Buy::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkSellByCoin($baseCoinId, $tradeCoinId)
{
    $item = Sell::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkTransactionByCoin($baseCoinId, $tradeCoinId)
{
    $item = Transaction::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}

function checkCoinPairDeleteCondition($coinPair)
{
    $response = ['success' => true, 'message' => __('Success')];
    $checkBuy = checkBuyByCoin($coinPair->parent_coin_id, $coinPair->child_coin_id);
    if ($checkBuy > 0) {
        return ['success' => false, 'message' => __('This coin pair already have some buy order, so you should not delete this pair.')];
    }
    $checkSell = checkSellByCoin($coinPair->parent_coin_id, $coinPair->child_coin_id);
    if ($checkSell > 0) {
        return ['success' => false, 'message' => __('This coin pair already have some sell order, so you should not delete this pair.')];
    }
    $checkOrder = checkTransactionByCoin($coinPair->parent_coin_id, $coinPair->child_coin_id);
    if ($checkOrder > 0) {
        return ['success' => false, 'message' => __('This coin pair already have some transaction, so you should not delete this pair.')];
    }

    return $response;
}
function checkCoinDeleteCondition($coin)
{
    $response = ['success' => true, 'message' => __('Success')];
    //    $checkCoinWalletBalance = checkWalletBalanceByCoin($coin->id);
    //    if ($checkCoinWalletBalance > 0) {
    //        return ['success' => false, 'message' => __('This coin wallet already have some balance, so you should not delete this coin.')];
    //    }
    $checkCoinWalletAddress = checkWalletAddressByCoin($coin->coin_type);
    if ($checkCoinWalletAddress > 0) {
        return ['success' => false, 'message' => __('This coin wallet already have some address, so you should not delete this coin.')];
    }
    $checkCoinPair = checkPairByCoin($coin->id);
    if ($checkCoinPair > 0) {
        return ['success' => false, 'message' => __('This coin already have coin pair, so first delete that pair then try again')];
    }
    $checkCoinDeposit = checkDepositByCoin($coin->coin_type);
    if ($checkCoinDeposit > 0) {
        return ['success' => false, 'message' => __('This coin already have some deposit, so you should not delete this coin')];
    }
    $checkCoinWithdrawal = checkWithdrawalByCoin($coin->coin_type);
    if ($checkCoinWithdrawal > 0) {
        return ['success' => false, 'message' => __('This coin already have some withdrawal, so you should not delete this coin')];
    }
    return $response;
}

function checkCoinTypeUpdateCondition($coin)
{
    $response = ['success' => true, 'message' => __('Success')];
    $checkCoinWalletBalance = checkWalletBalanceByCoin($coin->id);
    //    if ($checkCoinWalletBalance > 0) {
    //        return ['success' => false, 'message' => __('This coin type wallet already have some balance, so you should not change this coin type.')];
    //    }
    $checkCoinWalletAddress = checkWalletAddressByCoin($coin->coin_type);
    if ($checkCoinWalletAddress > 0) {
        return ['success' => false, 'message' => __('This coin type wallet already have some address, so you should not change this coin type.')];
    }
    $checkCoinPair = checkPairByCoin($coin->id);
    if ($checkCoinPair > 0) {
        return ['success' => false, 'message' => __('This coin type already have coin pair, so you should not change the coin Type, first delete that pair then try again')];
    }
    $checkCoinDeposit = checkDepositByCoin($coin->coin_type);
    if ($checkCoinDeposit > 0) {
        return ['success' => false, 'message' => __('This coin type already have some deposit, so you should not change this coin type')];
    }
    $checkCoinWithdrawal = checkWithdrawalByCoin($coin->coin_type);
    if ($checkCoinWithdrawal > 0) {
        return ['success' => false, 'message' => __('This coin type already have some withdrawal, so you should not change this coin type')];
    }
    return $response;
}

function checkCoinNetworkUpdateCondition($coin)
{
    $response = ['success' => true, 'message' => __('Success')];
    //    $checkCoinWalletBalance = checkWalletBalanceByCoin($coin->id);
    //    if ($checkCoinWalletBalance > 0) {
    //        return ['success' => false, 'message' => __('This coin network wallet already have some balance, so you should not change this coin network.')];
    //    }
    $checkCoinWalletAddress = checkWalletAddressByCoin($coin->coin_type);
    if ($checkCoinWalletAddress > 0) {
        return ['success' => false, 'message' => __('This coin network wallet already have some address, so you should not change this coin network.')];
    }
    $checkCoinDeposit = checkDepositByCoin($coin->coin_type);
    if ($checkCoinDeposit > 0) {
        return ['success' => false, 'message' => __('This coin network already have some deposit, so you should not change this coin network')];
    }
    $checkCoinWithdrawal = checkWithdrawalByCoin($coin->coin_type);
    if ($checkCoinWithdrawal > 0) {
        return ['success' => false, 'message' => __('This coin network already have some withdrawal, so you should not change this coin network')];
    }
    return $response;
}

function checkPair($baseCoinId, $tradeCoinId)
{
    $pair = CoinPair::where(['parent_coin_id' => $baseCoinId, 'child_coin_id' => $tradeCoinId, 'status' => STATUS_ACTIVE])->first();
    if ($pair) {
        return $pair;
    }
    return false;
}

function checkFuturePair($baseCoinId, $tradeCoinId)
{
    $pair = CoinPair::where([
        'parent_coin_id' => $baseCoinId,
        'child_coin_id' => $tradeCoinId,
        'enable_future_trade' => STATUS_ACTIVE,
        'status' => STATUS_ACTIVE,
    ])->first();
    if ($pair) {
        return true;
    }
    return false;
}

function getFirstPair()
{
    $pair = CoinPair::where(['status' => STATUS_ACTIVE])->orderBy('is_default', 'desc')->first();
    if ($pair) {
        return $pair;
    }
    return false;
}

// get fiat currency method
function get_fiat_currency_method($payment_method_id)
{
    $paymentMethod = CurrencyDepositPaymentMethod::where(['id' => $payment_method_id, 'status' => STATUS_SUCCESS])->first();
    if ($paymentMethod) {
        return $paymentMethod->payment_method;
    }
    return BANK_DEPOSIT;
}

function getEnabledKYCType($type)
{
    $response = ['success' => false, 'message' => __('Verification status off')];
    $kyc_type_is = allsetting('kyc_type_is') ?? 0;

    if ($type == 'kyc_withdrawal_setting_status') {
        $data = AdminSetting::where('slug', $type)->first();
        if (isset($data) && $data->value == STATUS_ACTIVE) {
            $response = ['success' => true, 'message' => __('Verification Type '), 'data' => $kyc_type_is];
        } else {
            $response = ['success' => false, 'message' => __('Verification status off')];
        }
    } elseif ($type == 'kyc_trade_setting_status') {
        $data = AdminSetting::where('slug', $type)->first();
        if (isset($data) && $data->value == STATUS_ACTIVE) {
            $response = ['success' => true, 'message' => __('Verification Type '), 'data' => $kyc_type_is];
        } else {
            $response = ['success' => false, 'message' => __('Verification status off')];
        }
    } else {
        $response = ['success' => false, 'message' => __('Your type is invalid')];
    }

    return $response;
}

function checkThirdPartyVerificationStatus($user)
{
    $checkUserPersonaVerificationDetails = ThirdPartyKycDetails::where('user_id', $user->id)->where('kyc_type', KYC_TYPE_PERSONA)->first();
    if (isset($checkUserPersonaVerificationDetails) && $checkUserPersonaVerificationDetails->is_verified == STATUS_SUCCESS) {
        $response = ['success' => true, 'message' => __('User Verification Status Active')];
    } else {
        $response = ['success' => false, 'message' => __('Please, Verify your KYC Verification')];
    }
    return $response;
}

function getKYCVerificationActiveList($type)
{
    $response = ['success' => false, 'message' => __('Verification status off')];
    if ($type == 'kyc_withdrawal_setting_status') {
        $activeList = AdminSetting::where('slug', 'kyc_withdrawal_setting_list')->first()->value;
        $response = ['success' => true, 'message' => __('Verification status on'), 'data' => $activeList];
    } elseif ($type == 'kyc_trade_setting_status') {
        $activeList = AdminSetting::where('slug', 'kyc_trade_setting_list')->first()->value;
        $response = ['success' => true, 'message' => __('Verification status on'), 'data' => $activeList];
    } elseif ($type == 'kyc_staking_setting_status') {
        $activeList = AdminSetting::where('slug', 'kyc_staking_setting_list')->first()->value;
        $response = ['success' => true, 'message' => __('Verification status on'), 'data' => $activeList];
    } else {
        $response = ['success' => false, 'message' => __('Your type is invalid')];
    }

    return $response;
}

// user verification list
function userVerificationActiveList($user)
{
    $userVerificationActiveList = [];
    if (isset($user)) {
        if ($user->phone_verified == STATUS_ACTIVE) {
            array_push($userVerificationActiveList, KYC_PHONE_VERIFICATION);
        }
        if ($user->is_verified == STATUS_ACTIVE) {
            array_push($userVerificationActiveList, KYC_EMAIL_VERIFICATION);
        }

        $verificationDetails = VerificationDetails::where('user_id', $user->id)->where('status', STATUS_ACCEPTED)->get();

        if (isset($verificationDetails)) {
            foreach ($verificationDetails as $item) {
                if ($item->field_name == 'pass_front') {
                    array_push($userVerificationActiveList, KYC_PASSPORT_VERIFICATION);
                }
                if ($item->field_name == 'drive_front') {
                    array_push($userVerificationActiveList, KYC_DRIVING_VERIFICATION);
                }
                if ($item->field_name == 'nid_front') {
                    array_push($userVerificationActiveList, KYC_NID_VERIFICATION);
                }
                if ($item->field_name == 'voter_front') {
                    array_push($userVerificationActiveList, KYC_VOTERS_CARD_VERIFICATION);
                }
            }
        }
    }
    return $userVerificationActiveList;
}


// check user agent
function checkUserAgent($request): string
{
    $agent = strtolower($request->header('User-Agent') ?? '');
    // Check for native mobile app headers
    if (str_contains($agent, 'mobile_app:')) {
        return str_contains($agent, 'mobile_app:android') ? 'android' : 'ios';
    }
    // If no app headers found, return 'web' for browser access
    return 'web';
}

// coin payment network fee
function network_fees_coinPayment($coinType)
{
    $fees = 0;
    $coin = CoinPaymentNetworkFee::where(['coin_type' => $coinType])->first();
    if (isset($coin)) {
        $fees = $coin->tx_fee;
    }
    return $fees;
}

// setting tab
function settingTab($request)
{
    $tab = 'general';
    if (isset($request->maintenance_mode_status)) {
        $tab = 'maintenance_mode';
    } elseif (isset($request->exchange_layout_view)) {
        $tab = 'exchange_layout';
    } elseif (isset($request->email_template_number)) {
        $tab = 'email_template';
    } elseif (isset($request->api_access_enable)) {
        $tab = 'own_api';
    }
    return $tab;
}

// total withdrawal earning
function withdrawalEarnings($coins)
{
    $withdrawals = WithdrawHistory::selectRaw('sum(fees) as fees,coin_type')
        ->groupBy('withdraw_histories.coin_type')
        ->get();
    $withdrawalFees = [];
    if (isset($withdrawals[0])) {
        foreach ($withdrawals as $w) {
            $data['withdrawal'][$w->coin_type] = $w->fees;
        }
    }
    if (isset($coins[0])) {
        foreach ($coins as $coin) {
            $withdrawalFees[] = [
                'id' => $coin->id,
                'name' => $coin->name,
                'coin_type' => $coin->coin_type,
                'fees' => isset($data['withdrawal'][$coin->coin_type]) ? $data['withdrawal'][$coin->coin_type] : 0
            ];
        }
    }
    return $withdrawalFees;
}

// total withdrawal earning
function tradeEarnings($coins)
{
    $withdrawals = Transaction::selectRaw('sum(buy_fees + sell_fees) as fees,coin_type')
        ->groupBy('withdraw_histories.coin_type')
        ->get();
    $withdrawalFees = [];
    if (isset($withdrawals[0])) {
        foreach ($withdrawals as $w) {
            $data['withdrawal'][$w->coin_type] = $w->fees;
        }
    }
    if (isset($coins[0])) {
        foreach ($coins as $coin) {
            $withdrawalFees[] = [
                'id' => $coin->id,
                'name' => $coin->name,
                'coin_type' => $coin->coin_type,
                'fees' => isset($data['withdrawal'][$coin->coin_type]) ? $data['withdrawal'][$coin->coin_type] : 0
            ];
        }
    }
    return $withdrawalFees;
}

function getCalculatedFees($amount)
{
    $setting = settings(['fiat_withdrawal_type', 'fiat_withdrawal_value']);
    try {
        $fees = (!isset($setting['fiat_withdrawal_type']) ? 1 : (!isset($setting['fiat_withdrawal_value']) ? 0 : ($setting['fiat_withdrawal_value'] <= 0 ? 0 : ($setting['fiat_withdrawal_type'] == Fiat_Withdraw_FIXED ? $setting['fiat_withdrawal_value'] : (bcdivx(bcmulx($amount, $setting['fiat_withdrawal_value'], 8), 100, 8))))));
        return $fees;
    } catch (\Exception $e) {
        storeException('getCalculatedFees', $e->getMessage());
        return 0;
    }
}

// get buy sell last amount
function getBuySellLastAmount($data, $baseCoinId, $tradeCoinId)
{
    $div = pow(10, 8);

    if ($data['buy_price'] >= 1) {
        $datas['buy_amount'] = custom_number_format(rand(0.0000001 * $div, 0.00001 * $div) / $div);
    } else {
        $datas['buy_amount'] = custom_number_format(rand(0.001 * $div, 0.1 * $div) / $div);
    }
    if ($data['sell_price'] >= 1) {
        $datas['sell_amount'] = custom_number_format(rand(0.0000001 * $div, 0.00001 * $div) / $div);
    } else {
        $datas['sell_amount'] = custom_number_format(rand(0.001 * $div, 0.1 * $div) / $div);
    }

    //    $buy = Buy::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->orderBy('id','desc')->first();
    //    $sell = Sell::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->orderBy('id','desc')->first();
    //    if (!empty($buy)) {
    //        $somePercent = bcmulx($buy->amount,0.35,8);
    //        $div = pow(10, 8);
    //        $data['buy_amount'] = custom_number_format(rand($somePercent * $div, $buy->amount * $div) / $div);
    //    }
    //    if (!empty($sell)) {
    //        $somePercent = bcmulx($buy->amount,0.35,8);
    //        $div = pow(10, 8);
    //        $data['sell_amount'] = custom_number_format(rand($somePercent * $div, $sell->amount * $div) / $div);
    //    }
    return $datas;
}

//last seen
function lastSeenStatus($user_id)
{
    $user = User::find($user_id);
    $data = [];
    if (isset($user)) {
        if ($user->last_seen) {
            $data['last_seen'] = Carbon::parse($user->last_seen)->diffForHumans();
            $data['online_status'] = STATUS_ONLINE;
            $response = ['success' => true, 'message' => __('Online Status'), 'data' => $data];
        } else {
            $data['last_seen'] = null;
            $data['online_status'] = STATUS_OFF_ONLINE;
            $response = ['success' => true, 'message' => __('Online Status'), 'data' => $data];
        }
    } else {
        $response = ['success' => false, 'message' => __('User not found'), 'data' => $data];
    }
    return $response;
}

/**
 * @param string $type Log type: DEBUG,WARN,ERROR etc...
 */
function storeBotException($type, $message)
{
    if (env('BOT_LOG_PRINT_ENABLE') == 1) {
        storeException($type, $message, 2);
    }
}

function getPriceFromApi($pair)
{
    $response = responseData(false);
    try {
        $client = new Client();
        //        $callApi = $client->get('https://api.binance.com/api/v3/depth?limit=10&symbol=' . $pair);
        $callApi = $client->get('https://whitebit.com/api/v4/public/orderbook/' . $pair . '?limit=1&level=2');
        $getBody = json_decode($callApi->getBody());
        if (!empty($getBody) && count($getBody->asks) > 0 && count($getBody->bids) > 0) {
            $data['sellData'] = $getBody->asks;
            $data['buyData'] = $getBody->bids;
            $data['price'] = $data['sellData'][0][0];
            $response = responseData(true, __('Success'), $data);
        } else {
            storeBotException('callAskBidApi ' . $pair, 'api has no data');
            $response = responseData(false, __('Api has no data'));
        }
    } catch (\Exception $e) {
        storeException('getPriceFromApi ex -> ' . $pair, $e->getMessage());

        $response = responseData(false, $e->getMessage());
    }
    return $response;
}

// check token for coin price
function checkNetworkCoinPrice($baseCoin, $tradeCoin)
{
    $isToken = 0;
    $base = Coin::find($baseCoin);
    if ($base->network == ERC20_TOKEN || $base->network == BEP20_TOKEN || $base->network == TRC20_TOKEN) {
        $isToken = 1;
    }
    $trade = Coin::find($tradeCoin);
    if ($trade->network == ERC20_TOKEN || $trade->network == BEP20_TOKEN || $trade->network == TRC20_TOKEN) {
        $isToken = 1;
    }
    return $isToken;
}

function getAllCoinList()
{
    return Coin::where('status', STATUS_ACTIVE)->get();
}

// update admin wallet balance
function updateAdminWalletBalance($adminId, $baseCoinId, $tradeCoinId, $baseCoinType, $tradeCoinType)
{
    $baseWallet = Wallet::firstOrCreate(['user_id' => $adminId, 'coin_id' => $baseCoinId], [
        'name' => $baseCoinType . ' wallet',
        'coin_type' => $baseCoinType
    ]);
    if (isset($baseWallet) && ($baseWallet->balance < 100000)) {
        $baseWallet->increment('balance', 100000);
    }
    $tradeWallet = Wallet::firstOrCreate(['user_id' => $adminId, 'coin_id' => $tradeCoinId], [
        'name' => $tradeCoinType . ' wallet',
        'coin_type' => $tradeCoinType
    ]);
    if (isset($tradeWallet) && ($tradeWallet->balance < 100000)) {
        $tradeWallet->increment('balance', 100000);
    }
}

// check coin withdrawal admin approval
function checkCryptoAdminApproval($amount, $coinId)
{
    $coin = Coin::find($coinId);
    if ($coin->admin_approval == STATUS_ACTIVE) {
        if ($amount > $coin->max_send_limit) {
            return true;
        }
    }
    return false;
}

function emailTemplateName($fileName)
{
    $template = 'email.' . $fileName;
    $template_number = allsetting('email_template_number') ?? 1;
    if ($template_number == EMAIL_TEMPLATE_NUMBER_ONE) {
        $template = 'email.' . $fileName;
    } elseif ($template_number == EMAIL_TEMPLATE_NUMBER_TWO) {
        $template = 'email.template-one.' . $fileName;
    } elseif ($template_number == EMAIL_TEMPLATE_NUMBER_THREE) {
        $template = 'email.template-two.' . $fileName;
    } elseif ($template_number == EMAIL_TEMPLATE_NUMBER_FOUR) {
        $template = 'email.template-three.' . $fileName;
    }

    return $template;
}

function generateUID()
{
    return uniqid() . date('') . time();
}

function getTotalStakingBonus($offer_details, $amount)
{
    $total_bonus = 0;
    if ($amount > 0) {
        $total_bonus = ($offer_details->offer_percentage * $offer_details->period * $amount) / (100 * 365);
    }

    return $total_bonus;
}

function userDeleteRequestAccepted($id, $item)
{
    $html = '<li class="deleteuser"><a title="' . __('Accepted') . '" href="#delete_request_' . ($id) . '" data-toggle="modal" class="btn btn-success btn-sm"><span><i class="fa fa-trash pr-1"></i>' . __(' Accepted') . '</span></a> </li>';
    $html .= '<div id="delete_request_' . ($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-md">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><div class="col-md-12"><div class="row">';
    $html .= '<div class="col-md-12"> <span>' . __('Request Reason') . ':' . '</span> <p>' . $item->delete_request_reason . '</p> </div>';
    $html .= '<div class="col-md-12"><a class="btn btn-primary m-1"href="' . route('adminUserDeleteRequestDeactive', $id) . '">' . __('Deactivate') . '</a>';
    $html .= '<a class="btn btn-warning m-1"href="' . route('adminUserDeleteRequestSoftDelete', $id) . '">' . __('Soft Delete') . '</a>';
    $html .= '<a class="btn btn-danger m-1"href="' . route('adminUserDeleteRequestForceDelete', $id) . '">' . __('Force Delete') . '</a>';
    $html .= '</div></div></div></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

//create future wallet
function create_future_wallet($user_id)
{
    $missingCoins = getMissingFutureCoinWallet($user_id);
    if (!empty($missingCoins)) {
        foreach ($missingCoins as $item) {
            storeNewFutureWallet($item);
        }
    }
}

//get missing future coin wallet list
function getMissingFutureCoinWallet($user_id)
{
    $coins = Coin::where(['status' => STATUS_ACTIVE])->get();
    $data = [];
    if (isset($coins[0])) {
        foreach ($coins as $coin) {
            $exist = FutureWallet::where(['user_id' => $user_id, 'coin_id' => $coin->id])->first();
            if (isset($exist)) {
            } else {
                $data[] = [
                    'coin_id' => $coin->id,
                    'coin_type' => $coin->coin_type,
                    'user_id' => $user_id,
                    'name' => $coin->coin_type . ' wallet',
                ];
            }
        }
    }
    return $data;
}

//sotre future wallet
function storeNewFutureWallet($item)
{
    $checkWallet = FutureWallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id'], 'coin_type' => $item['coin_type']])->first();

    if (!isset($checkWallet)) {
        $future_wallet = new FutureWallet;
        $future_wallet->wallet_name = $item['name'];
        $future_wallet->user_id = $item['user_id'];
        $future_wallet->coin_id = $item['coin_id'];
        $future_wallet->coin_type = $item['coin_type'];
        $future_wallet->save();
    }
}

function futureTradeFees($totalAmount)
{
    $fees = [
        'maker_fees' => 0,
        'taker_fees' => 0
    ];

    $limits = AdminSetting::where('slug', 'like', 'trade_limit_%')->get();

    $slugs = [];
    foreach ($limits as $limit) {
        if (bccompx($totalAmount, $limit->value) !== -1) {
            $slugs[] = 'maker_' . explode('_', $limit->slug)[2];
            $slugs[] = 'taker_' . explode('_', $limit->slug)[2];
            $adminSetting = allsetting($slugs);
            $fees['maker_fees'] = $adminSetting['maker_' . explode('_', $limit->slug)[2]];
            $fees['taker_fees'] = $adminSetting['taker_' . explode('_', $limit->slug)[2]];
        }
    }

    return $fees;
}

function calculatePositionForLimitOrderFuture($amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount)
{
    $quantity = $amount;

    if ($amount_type == AMOUNT_TYPE_BASE) {
        $quantity = bcdivx($amount, $orderPrice, 8);
    }

    $initialMargin = bcdivx(bcmulx($orderPrice, $quantity, 8), $leverage_amount, 8);

    $openLossLong = abs(min(0, 1 * ($marketPrice - $orderPrice)));
    $openLossShort = abs(min(0, -1 * ($marketPrice - $orderPrice)));

    $data['long_position'] = bcaddx($initialMargin, number_format($openLossLong, 8), 8);
    $data['short_position'] = bcaddx($initialMargin, number_format($openLossShort, 8), 8);

    return $data;
}

function calculatePositionForLimitOrderFutureMax($amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount)
{
    $quantity = $amount;

    // if($amount_type == AMOUNT_TYPE_BASE)
    // {
    //     $quantity = $amount / $orderPrice;
    // }

    $initialMargin = $orderPrice * $quantity;

    $openLossLong = abs(min(0, 1 * ($marketPrice - $orderPrice)));
    $openLossShort = abs(min(0, -1 * ($marketPrice - $orderPrice)));

    $data['long_position'] = $quantity;
    $data['short_position'] = $quantity;

    return $data;
}

function calculatePositionForMarketOrderFuture($buyPrice, $sellPrice, $orderPrice, $amount, $marketPrice, $amount_type, $leverage_amount)
{
    $quantity = $amount;
    if ($amount_type == AMOUNT_TYPE_BASE) {
        $quantity = bcdivx($amount, $orderPrice, 8);
    }
    $assumingPriceLong = $sellPrice * (1 + .05 / 100);
    $assumingPriceShort = $buyPrice;
    $initialMarginLong = bcdivx(bcmulx($assumingPriceLong, $quantity, 8), $leverage_amount, 8);
    $initialMarginShort = bcdivx(bcmulx($assumingPriceShort, $quantity, 8), $leverage_amount, 8);
    $openLossLong = abs(min(0, 1 * ($marketPrice - $assumingPriceLong)));
    $openLossShort = abs(min(0, -1 * ($marketPrice - $assumingPriceShort)));
    $openLossLongNew = bcmulx($quantity, number_format($openLossLong, 8), 8);
    $openLossShortNew = bcmulx($quantity, number_format($openLossShort, 8), 8);
    $data['long_position'] = bcaddx($initialMarginLong, $openLossLongNew, 8);
    $data['short_position'] = bcaddx($initialMarginShort, $openLossShortNew, 8);
    return $data;
}

function getTotalVolume($baseCoinId, $tradeCoinId)
{
    $buyOrderService = new BuyOrderService();
    $data['total_buy_amount'] = truncate_num($buyOrderService->getTotalAmount($baseCoinId, $tradeCoinId));
    $data['buy_price'] = truncate_num($buyOrderService->getPrice($baseCoinId, $tradeCoinId));
    $sellOrderService = new SellOrderService();
    $data['total_sell_amount'] = truncate_num($sellOrderService->getTotalAmount($baseCoinId, $tradeCoinId));
    $data['sell_price'] = truncate_num($sellOrderService->getPrice($baseCoinId, $tradeCoinId));

    return $data;
}

function calculateLiquidationPrice($request, $coinPairDetails)
{
    $liquidationPrice = isset($request->price) ? $request->price : $coinPairDetails->price;

    if ($request->side == TRADE_TYPE_BUY) {
        $liquidationPrice = bcsubx($liquidationPrice, bcdivx(bcmulx($liquidationPrice, 15, 8), 100, 8), 8);
    } else {
        $liquidationPrice = bcaddx($liquidationPrice, bcdivx(bcmulx($liquidationPrice, 15, 8), 100, 8), 8);
    }

    return $liquidationPrice;
}

function calculatePositionData($order_id, $exitPrice = null)
{
    $data['success'] = false;
    $data['pnl'] = 0;
    $data['roe'] = 0;
    $data['margin_ratio'] = 0;
    $data['symbol'] = '';
    $data['base_coin_type'] = '';
    $data['trade_coin_type'] = '';
    $data['market_price'] = 0;

    $orderDetails = FutureTradeLongShort::where('id', $order_id)->first();

    if (isset($orderDetails)) {
        $coinPairDetails = CoinPair::where('parent_coin_id', $orderDetails->base_coin_id)
            ->where('child_coin_id', $orderDetails->trade_coin_id)
            ->first();
        if (isset($coinPairDetails)) {
            $coinPairSymbol = coinPairSymbol($orderDetails->base_coin_id, $orderDetails->trade_coin_id);
            $marketPrice = $coinPairDetails->price;
            $data['success'] = true;
            $exitPrice = !is_null($exitPrice) ? $exitPrice : $marketPrice;
            $data['pnl'] = calculateFutureTradePNL($orderDetails, $exitPrice);
            $data['roe'] = calculateFutureTradeROE($orderDetails, $exitPrice);
            $data['margin_ratio'] = calculateFutureTradeMarginRatio($orderDetails, $exitPrice);
            $data['symbol'] = $coinPairSymbol['symbol'];
            $data['base_coin_type'] = $coinPairSymbol['base_coin_type'];
            $data['trade_coin_type'] = $coinPairSymbol['trade_coin_type'];
            $data['market_price'] = $marketPrice;
            $liquidPrice = $orderDetails->liquidation_price;
            if ($orderDetails->side == TRADE_TYPE_BUY) {
                if ($marketPrice <= $liquidPrice) {

                    $data['margin_ratio'] = 100;
                } else {
                    if ($liquidPrice > 0) {
                        $data['margin_ratio'] = bcmulx(bcdivx(bcsubx($marketPrice, $liquidPrice, 8), $marketPrice, 8), 100, 2);
                    } else {
                        $data['margin_ratio'] = 0;
                    }
                }
            } else {
                if ($marketPrice >= $orderDetails->liquidation_price) {
                    $data['margin_ratio'] = 100;
                } else {
                    if ($liquidPrice > 0) {
                        $data['margin_ratio'] = bcmulx(bcdivx(bcsubx($liquidPrice, $marketPrice, 8), $liquidPrice, 8), 100, 2);
                    } else {
                        $data['margin_ratio'] = 0;
                    }
                }
            }
        }
    }
    return $data;
}
function calculateFutureTradePNL($orderDetails, $exitPrice)
{
    $pnl = 0;


    if ($orderDetails->side == TRADE_TYPE_BUY) {
        $pnl = 1 * ($exitPrice - $orderDetails->entry_price) * $orderDetails->amount_in_trade_coin;
    } else {
        $pnl = -1 * ($exitPrice - $orderDetails->entry_price) * $orderDetails->amount_in_trade_coin;
    }

    return $pnl;
}

function calculateFutureTradeProfitLoss($side, $entry_price, $amount_in_trade_coin, $exitPrice)
{
    $pnl = 0;

    if ($side == TRADE_TYPE_BUY) {
        $pnl = bcmulx(1 * bcmulx(bcsubx($exitPrice, $entry_price, 8), $amount_in_trade_coin, 8), 8);
    } else {
        $pnl = -bcmulx(1 * bcmulx(bcsubx($exitPrice, $entry_price, 8), $amount_in_trade_coin, 8), 8);
    }

    return $pnl;
}

function calculateFutureTradeROE($orderDetails, $exitPrice = null)
{
    $roe = 0;
    if ($orderDetails->side == TRADE_TYPE_BUY) {
        $PNL = 1 * (1 - $orderDetails->entry_price / $exitPrice) * $orderDetails->amount_in_trade_coin;
        $initialMargin = $orderDetails->amount_in_trade_coin / $orderDetails->leverage;
        if ($PNL != 0) {
            $roe = ($PNL / $initialMargin) * 100;
        }
    } else {

        $PNL = -1 * ($exitPrice - $orderDetails->entry_price) * $orderDetails->amount_in_trade_coin;
        $initialMargin = $orderDetails->amount_in_trade_coin / $orderDetails->leverage;
        if ($PNL != 0) {
            $roe = ($PNL / $initialMargin) * 100;
        }
    }

    return $roe;
}

function calculateFutureTradeMarginRatio($orderDetails, $exitPrice = null)
{
    return 0;
}

function createFutureTradeTransaction($userId, $futureWalletId, $type, $amount, $coinType, $symbol = null, $coinPairId = null, $orderId = null)
{
    $transaction = new FutureTradeTransactionHistory;
    $transaction->order_id = $orderId;
    $transaction->user_id = $userId;
    $transaction->future_wallet_id = $futureWalletId;
    $transaction->coin_pair_id = $coinPairId;
    $transaction->type = $type;
    $transaction->amount = $amount;
    $transaction->coin_type = $coinType;
    $transaction->symbol = $symbol['symbol'];
    $transaction->save();

    return responseData(true, __('Transaction is created!'));
}

function coinPairSymbol($baseCoinId, $tradeCoinId)
{
    $data['base_coin_type'] = get_coin_type($baseCoinId);
    $data['trade_coin_type'] = get_coin_type($tradeCoinId);
    $data['symbol'] = $data['trade_coin_type'] . $data['base_coin_type'];

    return $data;
}

function storeCloseOrderStopLossTakeProfit($orderDetails, $tradeType, $profit)
{
    $newOrder = new FutureTradeLongShort;
    $newOrder->side = $orderDetails->side;
    $newOrder->parent_id = $orderDetails->id;
    $newOrder->amount_in_base_coin = $orderDetails->amount_in_base_coin;
    $newOrder->amount_in_trade_coin = $orderDetails->amount_in_trade_coin;
    $newOrder->margin = $orderDetails->margin;
    $newOrder->uid = generateUID();
    $newOrder->user_id = $orderDetails->user_id;
    $newOrder->base_coin_id = $orderDetails->base_coin_id;
    $newOrder->trade_coin_id = $orderDetails->trade_coin_id;
    $newOrder->entry_price = $orderDetails->entry_price;
    $newOrder->price = $orderDetails->price;
    $newOrder->take_profit_price = ($tradeType == FUTURE_TRADE_TYPE_TAKE_PROFIT_CLOSE) ? $profit : 0;
    $newOrder->stop_loss_price = ($tradeType == FUTURE_TRADE_TYPE_STOP_LOSS_CLOSE) ? $profit : 0;
    $newOrder->liquidation_price = $orderDetails->liquidation_price;
    $newOrder->fees = $orderDetails->fees;
    $newOrder->comission = $orderDetails->comission;
    $newOrder->leverage = $orderDetails->leverage;
    $newOrder->margin_mode = $orderDetails->margin_mode;
    $newOrder->trade_type = $tradeType;
    // $newOrder->is_position = $orderDetails->is_position;
    $newOrder->is_market = $orderDetails->is_market;
    $newOrder->order_type = $orderDetails->order_type;
    $newOrder->save();

    return responseData(true, __('Close order is created for Stop loss and take Profit!'));
}
function getCurrentUserId()
{
    return auth()->id() ?? auth()->guard('api')->id();
}


function calculateCostForFutureTrade($coinPairDetails, $order_type, $amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount)
{
    $orderData = getTotalVolume($coinPairDetails->parent_coin_id, $coinPairDetails->child_coin_id);
    $buyPrice = ($orderData['buy_price'] == 0) ? $coinPairDetails->price : $orderData['buy_price'];
    $sellPrice = ($orderData['sell_price'] == 0) ? $coinPairDetails->price : $orderData['sell_price'];;

    if ($order_type == LIMIT_ORDER) {
        $cost = calculatePositionForLimitOrderFuture($amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount);
    } else {
        $cost = calculatePositionForMarketOrderFuture($buyPrice, $sellPrice, $orderPrice, $amount, $marketPrice, $amount_type, $leverage_amount);
    }

    $data['longCost'] = $cost['long_position'];
    $data['shortCost'] = $cost['short_position'];

    $fundingFeesLong = bcdivx(bcmulx($data['longCost'], $coinPairDetails->leverage_fee, 8), 100, 8);
    $fundingFeesShort = bcdivx(bcmulx($data['shortCost'], $coinPairDetails->leverage_fee, 8), 100, 8);

    $futureTradeCommissionDataLong = futureTradeFees($data['longCost']);
    $futureTradeCommissionDataShort = futureTradeFees($data['shortCost']);

    $commissionFeesLong = bcmulx($data['longCost'], bcmulx($futureTradeCommissionDataLong['maker_fees'], 0.01, 8), 8);
    $commissionFeesShort = bcmulx($data['shortCost'], bcmulx($futureTradeCommissionDataShort['taker_fees'], .01, 8), 8);

    $totalCostLong = bcaddx($data['longCost'], bcaddx($fundingFeesLong, $commissionFeesLong, 8), 8);
    $totalCostShort = bcaddx($data['shortCost'], bcaddx($fundingFeesShort, $commissionFeesShort, 8), 8);


    $data['buyPrice'] = $buyPrice;
    $data['sellPrice'] = $sellPrice;

    $data['fundingFeesLong'] = $fundingFeesLong;
    $data['fundingFeesShort'] = $fundingFeesShort;
    $data['commissionFeesLong'] = $commissionFeesLong;
    $data['commissionFeesShort'] = $commissionFeesShort;
    $data['totalCostLong'] = $totalCostLong;
    $data['totalCostShort'] = $totalCostShort;

    return $data;
}

function calculateMaxCostForFutureTrade($coinPairDetails, $order_type, $amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount)
{
    $orderData = getTotalVolume($coinPairDetails->parent_coin_id, $coinPairDetails->child_coin_id);
    $buyPrice = ($orderData['buy_price'] == 0) ? $coinPairDetails->price : $orderData['buy_price'];
    $sellPrice = ($orderData['sell_price'] == 0) ? $coinPairDetails->price : $orderData['sell_price'];;

    if ($order_type == LIMIT_ORDER) {
        $cost = calculatePositionForLimitOrderFutureMax($amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount);
    } else {
        $cost = calculatePositionForLimitOrderFutureMax($amount, $marketPrice, $orderPrice, $amount_type, $leverage_amount);
    }

    $data['longCost'] = $cost['long_position'];
    $data['shortCost'] = $cost['short_position'];

    $fundingFeesLong = bcdivx(bcmulx($data['longCost'], $coinPairDetails->leverage_fee, 8), 100, 8);
    $fundingFeesShort = bcdivx(bcmulx($data['shortCost'], $coinPairDetails->leverage_fee, 8), 100, 8);

    $futureTradeCommissionDataLong = futureTradeFees($data['longCost']);
    $futureTradeCommissionDataShort = futureTradeFees($data['shortCost']);

    $commissionFeesLong = bcmulx($data['longCost'], bcmulx($futureTradeCommissionDataLong['maker_fees'], 0.01, 8), 8);
    $commissionFeesShort = bcmulx($data['shortCost'], bcmulx($futureTradeCommissionDataShort['maker_fees'], .01, 8), 8);

    $totalCostLong = bcsubx($data['longCost'], bcaddx($fundingFeesLong, $commissionFeesLong, 8), 8);
    $totalCostShort = bcsubx($data['shortCost'], bcaddx($fundingFeesShort, $commissionFeesShort, 8), 8);

    $maxSizeOpenLongTrade = bcdivx($totalCostLong, $sellPrice, 8);
    $maxSizeOpenShortTrade = bcdivx($totalCostLong, $buyPrice, 8);

    $data['buyPrice'] = $buyPrice;
    $data['sellPrice'] = $sellPrice;

    $data['fundingFeesLong'] = $fundingFeesLong;
    $data['fundingFeesShort'] = $fundingFeesShort;
    $data['commissionFeesLong'] = $commissionFeesLong;
    $data['commissionFeesShort'] = $commissionFeesShort;
    $data['totalCostLong'] = $totalCostLong;
    $data['totalCostShort'] = $totalCostShort;
    $data['totalCostLongTrade'] = $maxSizeOpenLongTrade;
    $data['totalCostShortTrade'] = $maxSizeOpenShortTrade;

    return $data;
}

function getHighestVolumePair()
{
    $longPercentage = 0;
    $shortPercentage = 0;
    $coinPair = '';
    $ratio = 0;
    $coin = CoinPair::select(
        'coin_pairs.id as coin_pair_id',
        'parent_coin_id',
        'child_coin_id',
        DB::raw("visualNumberFormat(price) as last_price"),
        DB::raw("visualNumberFormat(0) as balance"),
        'change as price_change',
        'volume',
        'high',
        'low',
        'child_coin.coin_type as child_coin_name',
        'parent_coin.coin_type as parent_coin_name',
        'child_coin.name as child_full_name',
        'parent_coin.name as parent_full_name',
        'child_coin.coin_icon'
    )
        ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
        ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
        ->where('coin_pairs.status', STATUS_ACTIVE)
        ->where('coin_pairs.enable_future_trade', STATUS_ACTIVE)
        ->orderBy('coin_pairs.volume', 'desc')
        ->first();
    if ($coin) {
        $coinPair = $coin->child_coin_name . $coin->parent_coin_name;
        $longOrder = FutureTradeLongShort::where('base_coin_id', $coin->parent_coin_id)
            ->where('trade_coin_id', $coin->child_coin_id)
            ->whereNotNull('parent_id')
            ->where('trade_type', FUTURE_TRADE_TYPE_OPEN)
            ->where('is_position', STATUS_PENDING)
            ->where('take_profit_price', 0)
            ->where('stop_loss_price', 0)
            ->sum('executed_amount');

        $shortOrder = FutureTradeLongShort::where('base_coin_id', $coin->parent_coin_id)
            ->where('trade_coin_id', $coin->child_coin_id)
            ->whereNotNull('parent_id')
            ->where('trade_type', FUTURE_TRADE_TYPE_CLOSE)
            ->where('is_position', STATUS_PENDING)
            ->where('take_profit_price', 0)
            ->where('stop_loss_price', 0)
            ->sum('executed_amount');

        if ($longOrder > 0) {
            $longPercentage = bcmulx(bcdivx($longOrder, bcaddx($longOrder, $shortOrder, 8), 8), 100, 8);
            $shortPercentage = bcsubx(100, $longPercentage, 8);
            if ($longPercentage > $shortPercentage) {
                $ratio = bcsubx($longPercentage, $shortPercentage, 8);
            } else {
                $ratio = bcsubx($shortPercentage, $longPercentage, 8);
            }
        }
    }

    $data['short_account'] = $shortPercentage;
    $data['long_account'] = $longPercentage;
    $data['ratio'] = $ratio;
    $data['coin_pair'] = $coinPair;
    $data['type'] = 'Perpetual';
    $data['hour'] = 24;
    return $data;
}

function getHighLowPNLByCoinPairGroup()
{
    $highest['coin_pair_id'] = 0;
    $highest['coin_type'] = '';
    $highest['symbol'] = '';
    $highest['total_amount'] = 0;

    $lowest['coin_pair_id'] = 0;
    $lowest['coin_type'] = '';
    $lowest['symbol'] = '';
    $lowest['total_amount'] = 0;

    $coin = CoinPair::select(
        'coin_pairs.id as coin_pair_id',
        'parent_coin_id',
        'child_coin_id',
        DB::raw("visualNumberFormat(price) as last_price"),
        DB::raw("visualNumberFormat(0) as balance"),
        'change as price_change',
        'volume',
        'high',
        'low',
        'child_coin.coin_type as child_coin_name',
        'parent_coin.coin_type as parent_coin_name',
        'child_coin.name as child_full_name',
        'parent_coin.name as parent_full_name',
        'child_coin.coin_icon'
    )
        ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
        ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
        ->where('coin_pairs.status', STATUS_ACTIVE)
        ->where('coin_pairs.enable_future_trade', STATUS_ACTIVE)
        ->orderBy('coin_pairs.volume', 'desc')
        ->first();

    if ($coin) {
        $highestPNL = FutureTradeTransactionHistory::where('type', FUTURE_TRADE_TRANSACTION_TYPE_REALIZED_PNL)
            ->where('amount', '>', 0)
            ->select('coin_pair_id', 'coin_type', 'symbol', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('coin_pair_id')
            ->first();

        if ($highestPNL) {
            $highest['coin_pair_id'] = $highestPNL->coin_pair_id;
            $highest['coin_type'] = $highestPNL->coin_type;
            $highest['symbol'] = $highestPNL->symbol;
            $highest['total_amount'] = $highestPNL->total_amount;
        }

        $lowestPNL = FutureTradeTransactionHistory::where('type', FUTURE_TRADE_TRANSACTION_TYPE_REALIZED_PNL)
            ->where('amount', '<', 0)
            ->select('coin_pair_id', 'coin_type', 'symbol', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('coin_pair_id')
            ->first();

        if ($lowestPNL) {
            $lowest['coin_pair_id'] = $lowestPNL->coin_pair_id;
            $lowest['coin_type'] = $lowestPNL->coin_type;
            $lowest['symbol'] = $lowestPNL->symbol;
            $lowest['total_amount'] = $lowestPNL->total_amount;
        }
    }

    $data['highest_PNL'] = $highest;
    $data['lowest_PNL'] = $lowest;

    return $data;
}

function convertCoinPriceToFiatCurrency($amount, $currencyDetails)
{
    $convertadedAmount = $amount;
    try {

        if ($currencyDetails->code == 'USD') {
            $convertadedAmount = $amount;
        } else {
            $convertadedAmount = bcmulx(bcdivx(1, $currencyDetails->rate, 8), $amount, 8);
        }
    } catch (\Exception $e) {
        storeException('convertCoinPriceToFiatCurrency ', $e->getMessage());
    }

    return $convertadedAmount;
}

function createImageUrl($path, $imageName)
{
    $return = null;
    if (isset($path) && isset($imageName)) {
        $return = asset($path) . '/' . $imageName;
    }
    return $return;
}

function custom_encrypt($data)
{
    try {
        $key = "681abfbdff0559329882f2a7960ec6d1b301851b58b4f9e33e57642abc46579e";
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($data, $nonce, sodium_hex2bin($key));
        $encryptedData = sodium_bin2base64($nonce . $ciphertext, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        return $encryptedData;
    } catch (\Exception $e) {
        storeException("custom_encrypt", $e->getMessage());
        throw $e;
    }
    //$key = sodium_crypto_secretbox_keygen(); // Generate a random key
    //echo sodium_bin2hex($key);
}

function custom_decrypt($data)
{
    try {
        $key = "681abfbdff0559329882f2a7960ec6d1b301851b58b4f9e33e57642abc46579e";
        $decodedData = sodium_base642bin($data, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        $nonce = substr($decodedData, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decodedData, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, sodium_hex2bin($key));
        return $plaintext !== false ? $plaintext : null;
    } catch (\Exception $e) {
        storeException("custom_decrypt", $e->getMessage());
        throw $e;
    }
}

function userCurrencyConvert($amount, $coin_type)
{
    $currency = Auth::user()->currency ?? "USD";
    return convert_currency($amount, 'USDT', $coin_type, $currency);
}

function formatScientificNotation($value) {
    // Remove commas and leading/trailing whitespace
    $value = str_replace(',', '', trim($value));

    // Check if it's in scientific notation
    if (preg_match('/^[\d\.]+[Ee][\-\+]\d+$/', $value)) {
        // Scientific notation value
        $convertedValue = sprintf('%.20f', (float)$value);
    } else {
        // Regular decimal value
        $convertedValue = number_format((float)$value, 10);
    }

    $result = rtrim(rtrim($convertedValue, '0'), '.');

    return $result;
}
// get coin base coin pair

function getCoinBaseCoinPair($coinId)
{
    $coinPairs = CoinPair::where('parent_coin_id', $coinId)
        ->orWhere('child_coin_id', $coinId)
        ->select(
            'coin_pairs.id',
            'parent_coin_id',
            'child_coin_id',
            DB::raw("TRUNCATE(`change`,2) as price_change"),
            'child_coin.coin_type as child_coin_name',
            'child_coin.coin_icon as icon',
            'parent_coin.coin_type as parent_coin_name',
            'child_coin.name as child_full_name',
            'parent_coin.name as parent_full_name',
            DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as pair_bin'),
            DB::raw('CONCAT(child_coin.coin_type,"_",parent_coin.coin_type) as coin_pair_coin')
        )
        ->join('coins as child_coin', ['coin_pairs.child_coin_id' => 'child_coin.id'])
        ->join('coins as parent_coin', ['coin_pairs.parent_coin_id' => 'parent_coin.id'])
        ->where(['coin_pairs.status' => STATUS_ACTIVE])
        ->orderBy('is_default', 'desc')
        ->get();
    if (isset($coinPairs[0])) {
        $coinPairs->each(function ($coin) {
            $coin->icon = show_image_path($coin->icon, 'coin/');
            $coin->coin_pair_id = $coin->id;
            $coin->coin_pair_name = $coin->child_coin_name . '/' . $coin->parent_coin_name;
            $coin->coin_pair = $coin->child_coin_name . '_' . $coin->parent_coin_name;
        });
    }
    return $coinPairs;
}


// get order highest price
function getHighestPriceFromOrderList($orders)
{
    $highest_price = 0;
    foreach ($orders as $item) {
        if ($item->total > $highest_price) {
            $highest_price = $item->total;
        }
    }
    return $highest_price;
}

// get order highest price
function getOrderPricePercentage($orders, $highestPrice)
{
    foreach ($orders as $order) {
        $order->percentage = bcmulx(bcdivx($order->total, $highestPrice, 8), 100, 8);
    }
}


function getCalculatedFiatDepositFees($amount)
{
    $setting = settings(['fiat_deposit_fees_type', 'fiat_deposit_fees_value']);
    try {
        $fees = (!isset($setting['fiat_deposit_fees_type']) ? 1 : (!isset($setting['fiat_deposit_fees_value']) ? 0 : ($setting['fiat_deposit_fees_value'] <= 0 ? 0 : ($setting['fiat_deposit_fees_type'] == Fiat_Withdraw_FIXED ? $setting['fiat_deposit_fees_value'] : (bcdivx(bcmulx($amount, $setting['fiat_deposit_fees_value'], 8), 100, 8))))));
        return $fees;
    } catch (\Exception $e) {
        storeException('getCalculatedFiatDepositFees', $e->getMessage());
        return 0;
    }
}

// coin api settings
function api_settings_new($input = null)
{
    $output = [
        BEP20_TOKEN => __('BEP20 Token Api'),
        BITCOIN_API => __('Bitcoin Api'),
        BITGO_API => __('Bitgo Api'),
        COIN_PAYMENT => __('Coin Payment Api'),
        ERC20_TOKEN => __('ERC20 Token Api'),
        MATIC_TOKEN => __('Polygon Token Api'),
        TRC20_TOKEN => __('TRC20 Token Api'),
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return @$output[$input] ?: __("Not found");
    }
}

// generate strong key
function generateStrongKey($length = 30)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!#$%&\()*+/:<=>?@[\\]^_{|}';
    $key = '';

    for ($i = 0; $i < $length; $i++) {
        $key .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $key;
}


function addWhiteListIp($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('Accept') . '" href="#htmlFormSending_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-check-circle-o" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="htmlFormSending_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Accept') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<form action="' . route($route) . '"method="post" enctype="multipart/form-data">';
    $html .= '<input type="hidden" name="_token" value="' . csrf_token() . '" />';
    $html .= '<input type="hidden" name="id" value="' . $id . '" />';
    $html .= '<div class="modal-body">';
    $html .= '<label>' . __('Upload Bank Slip') . '</label>';
    $html .= '<input type="file" name="file" class="form-control-file" required />';
    $html .= '</div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<button class="btn btn-danger" type="submit">' . __('Confirm') . '</button>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function selected_node_network($input = null)
{
    $output = [
        ERC20_TOKEN => __('ERC20'),
        BEP20_TOKEN => __('BEP20'),
        TRC20_TOKEN => __('TRC20'),
        MATIC_TOKEN => __('Polygon'),
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return isset($output[$input]) ? $output[$input] : __("Not found");
    }
}

function format_number($number, $decimal)
{
    return floor($number * pow(10, $decimal)) / pow(10, $decimal);
}

// bot operation
function bot_operation($input = null)
{
    $output = [
        BOT_RANDOM_MARKET_PRICE => __('Random'),
        BOT_INCREASE_MARKET_PRICE => __('Increase'),
        BOT_DECREASE_MARKET_PRICE => __('Decrease'),
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return isset($output[$input]) ? $output[$input] : __("Not found");
    }
}

function checkGoogleAuth()
{
    return env('GACAWKV', true) == true;
}

function themeColorsFromDb($array = null)
{
    if (!isset($array[0])) {
        $themeColors = ThemeColor::get();
        if ($themeColors) {
            $output = [];
            foreach ($themeColors as $color) {
                $output[$color->slug] = $color->value;
            }
            return $output;
        }
        return false;
    } elseif (is_array($array)) {
        $themeColors = ThemeColor::whereIn('slug', $array)->get();
        if ($themeColors) {
            $output = [];
            foreach ($themeColors as $color) {
                $output[$color->slug] = $color->value;
            }
            return $output;
        }
        return false;
    } else {
        $themeColors = ThemeColor::where(['slug' => $array])->first();
        if ($themeColors) {
            $output = $themeColors->value;
            return $output;
        }
        return false;
    }
}

function themeColors($input = null)
{
    $output = [
        THEME_GREEN => __('Green'),
        THEME_YELLOW => __('Yellow'),
        THEME_BLUE => __('Blue'),
    ];

    if (is_null($input)) {
        return $output;
    } else {
        return $output[$input] ?? "N/A";
    }
}

function get_super_admin_id()
{
    if (
        $user = User::where("status", STATUS_ACTIVE)
        ->where("role", STATUS_ACTIVE)
        ->where("super_admin", STATUS_ACTIVE)
        ->first()
    )
        return $user->id;
    return 0;
}

function transaction_filter_by($qeury_type = null)
{
    $output = [
        TRANSACTION_FILTER_ALL         => __('All'),
        TRANSACTION_FILTER_BOT_TO_BOT  => __('Bot To Bot'),
        TRANSACTION_FILTER_BOT_TO_USER => __('Bot To User'),
        TRANSACTION_FILTER_USER_TO_USER=> __('User To User'),
    ];

    if (is_null($qeury_type)) {
        return $output;
    } else {
        return $output[$qeury_type] ?? "N/A";
    }
}

if (!function_exists('storage_logs')) {
    /**
     * @return string
     */
    // image path
    function storage_logs()
    {
        return storage_path() . '/logs';
    }
}

function processExceptionMsg(Exception $e): string
{
    $full_path = $e->getFile();
    $file = basename($full_path);
    $directory = basename(dirname($full_path));
    $file = "$directory/$file";
    return $file . ':' . $e->getLine() . ' -> ' . $e->getMessage();
}
