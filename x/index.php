<?php
require_once('site_helper.php');
require_once('dbtest.php');

class indexx {
    protected $dbtest = null;
    protected $uri = null;

    public function __construct(){
        // parent::__construct();
        date_default_timezone_set("Asia/Bangkok");
        // $this->mainlib->logged_in();
        header('Access-Control-Allow-Origin: *'); 
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
        $this->dbtest = new dbtest();
        $this->uri = my_uri();
    }
    
    public function declar(){
        $db = $this->dbtest->declar();
        
        echo json_encode($db); exit;
    }
    
    public function antrian_rc(){
        $db = $this->dbtest->select_nomor_antridaftar_max(date('Y-m-d'));
        
        echo json_encode($val); exit;
    }
}

$indexx = new indexx();
$uri = my_uri();
print_r($uri);

$indexx->declar();

// // cek apakah ada method bernama $uri[0]
// if(method_exists($indexx, $uri[0] )) $indexx->$uri[0]();
// else die("not found.");
?>