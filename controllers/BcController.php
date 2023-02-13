<?php

require_once('./models/BcModel.php');

class BcController{
    public $BcModel = null;

    public function bccustoms(){

        $this->BcModel = new BcModel();
        $res = $this->BcModel->bccustoms();
        // return $qu;
        echo json_encode($res); exit;
    }
}