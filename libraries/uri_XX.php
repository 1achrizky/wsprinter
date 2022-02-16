<?php
class uri {
  public function __construct(){
		date_default_timezone_set("Asia/Bangkok");
		header('Access-Control-Allow-Origin: *'); 
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
  }
  
  public function segment($i=null){
    if (!defined('PATH_INFO')) define('PATH_INFO', str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']) );
    // define('PATH_INFO', str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['REQUEST_URI']) );
    // echo PATH_INFO;
    //print_r($_GET);
    
    $uri = [];
    if(strlen(PATH_INFO) != 0 ){
      if(PATH_INFO[0] == '/'){
        $uri =  explode("/", substr( $_SERVER['PATH_INFO'], 1) );
        return $uri;
        //echo "<br>===".$uri[0];
        // echo "<pre>", print_r($uri) ,"</pre>";
      }else return false;		
    }else return false;		
  }
}

?>