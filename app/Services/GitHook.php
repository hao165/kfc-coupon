<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class GitHook
{
    function __construct()
    {
        $this->hookSecret = config('app.gitHook'); // set NULL to disable check
        $this->log = 'git_hook';
    }

    /**
     * GitHub webhook handler template.
     *
     * @see     https://docs.github.com/webhooks/
     * @author  Miloslav Hůla (https://github.com/milo)
     */
    function handle()
    {
        Log::channel($this->log)->info(" - connect - ");

        set_error_handler(function ($severity, $message, $file, $line) {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function ($e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo "Error on line {$e->getLine()}: " . htmlSpecialChars($e->getMessage());
            die();
        });

        $rawPost = null;
        if ($this->hookSecret !== null) {
            if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
                throw new \Exception("HTTP header 'X-Hub-Signature' is missing.");
            } elseif (!extension_loaded('hash')) {
                throw new \Exception("Missing 'hash' extension to check the secret code validity.");
            }

            list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + array('', '');
            if (!in_array($algo, hash_algos(), true)) {
                throw new \Exception("Hash algorithm '$algo' is not supported.");
            }

            $rawPost = file_get_contents('php://input');
            if (!hash_equals($hash, hash_hmac($algo, $rawPost, $this->hookSecret))) {
                throw new \Exception('Hook secret does not match.');
            }
        };

        if (!isset($_SERVER['CONTENT_TYPE'])) {
            throw new \Exception("Missing HTTP 'Content-Type' header.");
        } elseif (!isset($_SERVER['HTTP_X_GITHUB_EVENT'])) {
            throw new \Exception("Missing HTTP 'X-Github-Event' header.");
        }

        switch ($_SERVER['CONTENT_TYPE']) {
            case 'application/json':
                $json = $rawPost ?: file_get_contents('php://input');
                break;

            case 'application/x-www-form-urlencoded':
                $json = $_POST['payload'];
                break;

            default:
                throw new \Exception("Unsupported content type: $_SERVER[CONTENT_TYPE]");
        }

        # Payload structure depends on triggered event
        # https://developer.github.com/v3/activity/events/types/
        $payload = json_decode($json);

        switch (strtolower($_SERVER['HTTP_X_GITHUB_EVENT'])) {
            case 'ping':
                echo 'pong';
                Log::channel($this->log)->info("* ping *");
                break;

            case 'push':
                shell_exec("cd /home/tech/web/kfc.izo.tw/public_html && git fetch --all && git reset --hard origin/master && git pull origin master && php ~/.composer/composer install && php artisan migrate --force && php artisan clear:cache && php ~/.composer/composer dump-autoload");
                Log::channel($this->log)->info("push.");
                break;

                //	case 'create':
                //		break;

            default:
                header('HTTP/1.0 404 Not Found');
                echo "Event:$_SERVER[HTTP_X_GITHUB_EVENT] Payload:\n";
                print_r($payload); # For debug only. Can be found in GitHub hook log.
                Log::channel($this->log)->info("404- Event:$_SERVER[HTTP_X_GITHUB_EVENT] Payload:$json");
                die();
        }

        Log::channel($this->log)->info(" - end - ");
    }
}
