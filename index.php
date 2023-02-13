<?php
require_once('helpers/site_helper.php');
// require_once('models/BcModel.php');
require_once('controllers/MyControllers.php');


// class wsprinter extends m_pdo {
// class Ws extends MyControllers {
class Ws extends BcController {
	protected $uri = null;

    public function __construct(){
        // parent::__construct();
        date_default_timezone_set("Asia/Bangkok");
        // $this->mainlib->logged_in();
        header('Access-Control-Allow-Origin: *'); 
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

        $this->uri = my_uri();
    }
    
}

$ws = new Ws();
// echo "<pre>",print_r($ws),"</pre>";
$uri = my_uri();
// echo "====$uri[0]===";
// print_r($uri);
$controller = $uri[0];

// cek apakah ada method bernama $uri[0]
if(method_exists($ws, $controller )) $ws->$controller();
else die("not found.");

?>