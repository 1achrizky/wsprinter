<?php
require_once('BcController.php');

class MyControllers{
    public $c=null;
    
    public function __construct(){
        $this->c = new BcController;
        // return $this->c;
    } 

    // fake "extends C" using magic function
    public function __call($method, $args){
        $this->c->$method($args[0]);
        // print_r([$method, $args]);
    }

}
?>