<?php

require_once('./helpers/site_helper.php');

class BcModel{
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
            // $db = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
            // $db = new PDO("sqlsrv:server=$server;TrustServerCertificate=true;ENCRYPT=true; database = $dbName", $uid, $pwd);
            $db = new PDO("sqlsrv:server=$host;TrustServerCertificate=true;ENCRYPT=true; database = $dbname", $username, $password);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e){
            die("Connection error: " . $e->getMessage());
        }
        return $db;
    }

    public function tes($url, $button_id, $fx_name){
        return [$url, $button_id, $fx_name];
    }

    public function bccustoms($url=null, $button_id=null, $fx_name=null){
        $db = $this->pdo_declare();

        $q = "SELECT TOP 10 * FROM BCCUSTOMSTABLE";

        $query = $db->prepare($q);
        $query->bindValue(1, $url );
        $query->bindValue(2, $button_id );
        $query->bindValue(3, $fx_name );
        $query->execute();
        $val = $query->fetchAll(PDO::FETCH_ASSOC);
        // return $val;
        if($val)
            // return $val;
            return ["metadata"=> ["code"=>200, "status"=>"success", "message"=>"OK"], "response"=>$val];
        else
            // return '';
            return ["metadata"=> ["code"=>201, "status"=>"failed", "message"=>"Tidak berhasil."], "response"=>null];    
        }

}
?>