<?php

class AppController{

    private $request;

    public function __construct(){
        $this->request = $_SERVER['REQUEST_METHOD'];
    }

    protected function isPost():bool{
        return $this->request ==='POST';
    }

    protected function isGet():bool{
        return $this->request ==='GET';
    }

    protected function authorize() {
        
        $publicRoutes = ['login', 'register', ''];
        $currentRoute = trim($_SERVER['REQUEST_URI'], '/');
        

        if (in_array($currentRoute, $publicRoutes)) {
            return;
        }


        if (!isset($_SESSION['user'])) {
            $_SESSION['error_message'] = 'You must be logged in to access this page.';
            header("Location: /login");
            exit();
        }
    }

    protected function render(string $template = null, array $variables = []){
        $templatePath = 'public/views/'.$template.'.php';
        $output = 'File not found';


        if(file_exists($templatePath)){
            extract($variables);


            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        }

        print $output;
    }
}