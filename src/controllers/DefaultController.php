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

    public function drivers(){
        $this->authorize();
        $this->render('drivers');
    }

    public function reports(){
        $this->authorize();
        $this->render('reports');
    }

    
}