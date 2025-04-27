<?php

require_once 'AppController.php';

class DefaultController extends AppController{

    public function index(){
        $this->render('login');
    }

    public function dashboard(){
        $this->render('dashboard');
    }

    public function vehicles(){
        $this->render('vehicles');
    }

    public function drivers(){
        $this->render('drivers');
    }

    public function map(){
        $this->render('map');
    }

    public function reports(){
        $this->render('reports');
    }

}