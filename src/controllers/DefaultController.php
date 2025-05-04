<?php

require_once 'AppController.php';

class DefaultController extends AppController{

    public function index() {
        if (isset($_SESSION['user'])) {
            header("Location: /dashboard");
            exit();
        }
        $this->render('login');
    }

    public function dashboard()
    {
        $this->authorize();
        $this->render('dashboard');
    }

    public function vehicles(){
        $this->authorize();
        $this->render('vehicles');
    }

    public function drivers(){
        $this->authorize();
        $this->render('drivers');
    }

    public function map(){
        $this->authorize();
        $this->render('map');
    }

    public function reports(){
        $this->authorize();
        $this->render('reports');
    }

    
}