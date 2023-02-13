<?php

require_once('site_helper.php');

class dbtest{
  protected $conf_db_pdo = [
  	"main" => [
  		"hostname" => "192.168.100.19",
	    "dbname" 	 => "AXETADEV",
	    "username" => "sa",
	    "password" => "albolabris",
  	],
  ];

  public function pdo_declare(){
		// $host = "192.168.1.5";
    //   $dbname = "xlink";
    //   $username = "root";
    //   $password = "root";

    $host = $this->conf_db_pdo['main']['hostname'];
    $dbname = $this->conf_db_pdo['main']['dbname'];
    $username = $this->conf_db_pdo['main']['username'];
    $password = $this->conf_db_pdo['main']['password'];
    try {
        $db = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e){
        die("Connection error: " . $e->getMessage());
    }
    return $db;
  }

  public function tes($url, $button_id, $fx_name){
    return [$url, $button_id, $fx_name];
  }
  
  
  public function declar(){
    $db = $this->pdo_declare();
    return $db;
  }

  public function select_nomor_antridaftar_max($date=null){
    $db = $this->pdo_declare();

    $q = "SELECT nomor from antridaftar WHERE date = ?	ORDER BY nomor desc	LIMIT 1";
    // $query = $this->db->query($q)->result_array();
    // $nominal = (count($query)>0) ?  (int)$query[0]['nomor'] : 0;

    $qu = $db->prepare($q);
    $qu->bindValue(1, $date );
    $qu->execute();
    $query = $qu->fetch(PDO::FETCH_ASSOC);
    // return $query;
    
    $nominal = (count($query)>0) ?  (int)$query['nomor'] : 0;
    // return $nominal;

    $res['now'] = nominal_terbilang($nominal);
    $res['next'] = nominal_terbilang($nominal+1);
    return $res;
  }
}
?>