<?php
namespace App\Http\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
// use App\Logger\CloudWatchLoggerFactory;

class Logger
{
    protected string $path;
    protected string $logger_type; // 'custom' or 'default'
    protected string $stack_idx;

    /**
     * @param string $file Custom file, not required
     * @param string $path Custom path, default storage/logs
     */
    public function __construct($file = null, $path = null, $stack_idx = 0)
    {
        $this->stack_idx = $stack_idx;
        if (is_null($file)) {
            $this->logger_type = 'default';
        } else {
            $this->path = (empty($path) ? storage_logs() : $path) . "/$file";
            $this->logger_type = 'custom';
        }
        if (!empty($this->path) && !(File::exists($this->path))) {
            File::put($this->path, '');
        }
        //$this->path = is_null($path) ? env('DEFAULT_LOG_PATH') : $path;
    }

    public function logErr(string $msg): void {
        $this->stack_idx = 1;
        $this->log('ERROR', $msg);
    }

    public function logWarn(string $msg): void {
        $this->stack_idx = 1;
        $this->log('WARNING', $msg);
    }

    public function logInfo(string $msg): void {
        $this->stack_idx = 1;
        $this->log('INFO', $msg);
    }

    /**
     * @param string $type Log type: INFO, WARNING, ERROR etc...
     */
    public function log(string $type, $msg = '', $timestamp = true)
    {
        $logPrint = env('LOG_PRINT_ENABLE') ?? 1;
        if ($type != strtoupper('ERROR') && $logPrint != 1) {
            return;
        }

        if (gettype($msg) == 'array' || gettype($msg) == 'object') {
            $msg = json_encode($msg);
        }

        $bt = debug_backtrace();
        // $caller = array_shift($bt);
        $caller = $bt[$this->stack_idx];
        
        $full_path = $caller['file'];
        $file = basename($full_path);
        $directory = basename(dirname($full_path));
        $file = "$directory/$file";

        $line = $caller['line'];
        $text = '';

        if ($timestamp) {
            $datetime = date("Y-m-d H:i:s");
            $text = "[$datetime]";
        }

        $appEnv = config('app.env');

        if ($this->logger_type == 'custom') {
            if (in_array(strtoupper($type), ['INFO', 'ERROR', 'WARNING'])) {
                $type = strtoupper($type);
                $text = "$text $appEnv.$type: $file:$line -> $msg \r\n\r";
            } else {
                $text = "$text $appEnv.INFO: $file:$line -> $type: $msg \r\n\r";
            }
            error_log($text, 3, $this->path);
        } else {
            if (in_array(strtoupper($type), ['INFO', 'ERROR', 'WARNING'])) {
                $type = strtoupper($type);
                $text = "$file:$line -> $msg \r\n\r";
            } else {
                $text = "$file:$line -> $type: $msg \r\n\r";
            }

            switch ($type) {
                case "WARNING":
                    Log::warning($text);
                    break;
                case "ERROR":
                    Log::error($text);
                    break;
                default:
                    Log::info($text);
            }
        }

        // if (env('LOG_CHANNEL') == 'cloudwatch') {
        //     dispatch(function () use($file, $line, $type, $msg, $datetime) {
        //         config()->set('logging.channels.cloudwatch.stream', basename($this->path));
        //         $cloudWatchLog = new CloudWatchLoggerFactory();
        //         $logger = $cloudWatchLog(config('logging.channels.cloudwatch'));
        //         $logger->addRecord($logger::INFO, "[$datetime] $file:$line -> $type: $msg \r\n\r");
        //     });
        // }
    }
}


// namespace App\Http\Services;

// use Illuminate\Support\Facades\File;
// use Illuminate\Support\Facades\Log;

// class Logger
// {

//     public function log($type, $text = '', $timestamp = true)
//     {
//         try {
//             $logPrint = env('LOG_PRINT_ENABLE') ?? 1;
//             if ($logPrint == 1) {
//                 if(gettype($text) == 'array'){
//                     $text = json_encode($text);
//                 }
//                 if ($timestamp) {
//                     $datetime = date("d-m-Y H:i:s");
//                     $text = "$datetime, $type: $text \r\n\r\n";
//                 } else {
//                     $text = "$type\r\n\r\n";
//                 }
//                 Log::info($text);
//             }
//         } catch (\Exception $e) {
//             Log::info("log exception");
//         }

//     }
// }
