<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DesktopController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * @param $string
     * @param $start
     * @param $end
     * @return bool|string
     * https://stackoverflow.com/questions/5696412/how-to-get-a-substring-between-two-strings-in-php
     */
    private function between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    /**
     * @return Factory|View
     */
    public function index() {
        // Bootstrap
        $bootstrap = null;
        $environment = config('app.env');

        // File exists
        if ($environment == "production" || $environment == "live" || $environment == "test") {
            $path = base_path("../" . config('app.client_base') . "/index.html");
            if (file_exists($path)) {
                $buffer = file_get_contents($path);
                if (strlen($buffer) > 0) {
                    $start = "type=\"text/javascript\">";
                    $end = "</script>";

                    // Split
                    $split = explode("<script id=\"microloader\"", $buffer);
                    if (sizeof($split) == 2) {
                        $bootstrap = $this->between($split[1], $start, $end);
                        if (strlen($bootstrap) <= 0)
                            $bootstrap = null;
                    }
                }
            }
        }

        return view ( 'view.desktop' , [
            "bootstrap" => $bootstrap
        ]);
    }
}
