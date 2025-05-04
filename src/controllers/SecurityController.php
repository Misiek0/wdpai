<?php

require_once 'AppController.php';
require_once __DIR__.'/../models/User.php';
require_once __DIR__.'/../repository/UserRepository.php';

class SecurityController extends AppController{

    private $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new UserRepository();
        
    }

    public function login(){

        if ($this->isGet() && isset($_SESSION['user'])) {
            header("Location: /dashboard");
            exit();
        }

        if(!$this->isPost()){
        $messages = [];
            if (isset($_SESSION['registration_success'])) {
                $messages[] = $_SESSION['registration_success'];
                unset($_SESSION['registration_success']);
            }

            return $this->render('login', ['messages' => $messages]); 
        }
        
       

        $email = $_POST['email'];
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->render('login', ['messages' => ['Invalid email format']]);
        }

        if(isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 3){
            if(!isset($_SESSION['login_block_time'])){
                $_SESSION['login_block_time'] = time();
            } elseif(time() - $_SESSION['login_block_time'] > 300) {
                unset($_SESSION['login_attempts']);
                unset($_SESSION['login_block_time']);
            } else {
                $remaining = 300 - (time() - $_SESSION['login_block_time']);
                return $this->render('login', [
                    'messages' => ["Account temporarily locked. Try again in ".ceil($remaining/60)." minutes."]
                ]);
            }
        }
        try{
            $user = $this->userRepository->getUser($email);

            if(!$user || !password_verify($password, $user->getPassword())){
                $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                return $this->render('login', ['messages' => ['Invalid credentials']]);
            }

            unset($_SESSION['login_attempts']);
            unset($_SESSION['login_block_time']);

            $_SESSION['user'] = [
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'surname' => $user->getSurname()
            ];
            header("Location: /dashboard");
            exit(); 

        }catch(Exception $e){
            $this->render('login',['messages' => ['Service unavailable. Please try later']]);            
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        header("Location: /login");
        exit();
    }

    public function register(){
        
        if(!$this->isPost()){
            return $this->render('register');

        }

        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirmedPassword = $_POST['confirmedPassword'];

        try{
            if (strlen($password) < 8) {
                return $this->render('register', ['messages' => ['Password must be at least 8 characters']]);
            }
            if ($password !== $confirmedPassword) {
                return $this->render('register', ['messages' => ['Please provide proper password']]);
            }

            $user = new User($email, $password, $name, $surname);
            

            $this->userRepository->addUser($name, $surname, $email, $password);

            $_SESSION['registration_success'] = 'Registration successful! You can now log in.';
            header('Location: /login');
            exit();

        }catch(PDOException $e){
            $error = ($e->getCode() == '23505')
                ?'Email already exists'
                :'Operation Sign in failed. Please try again';

                $this->render('register', ['messages' => [$error]]);


        }catch (Exception $e) {
            $this->render('register', ['messages' => [$e->getMessage()]]);
        }
        
    }
}