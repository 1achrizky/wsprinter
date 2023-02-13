<?php

require_once('./models/BcModel.php');

class XController{
    public $BcModel = null;

    public function bccustoms(){

        $this->BcModel = new BcModel();
        $qu = $this->BcModel->bccustoms();
        // return $qu;
        echo json_encode($qu); exit;
    }
}