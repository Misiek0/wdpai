<?php

require_once 'Repository.php';
require_once __DIR__.'/../models/User.php';


class UserRepository extends Repository{

    public function getUser(string $email): ?User{
        $stmt = $this->database->connect()->prepare('
        SELECT * FROM public.users WHERE email = :email
        ');

        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user == false){
            return null;

        }

        return new User(
            $user['email'],
            $user['password'],
            $user['name'],
            $user['surname']
        );

    }

    public function addUser(string $name, string $surname, string $email, string $password): bool {
        try {
            $stmt = $this->database->connect()->prepare('
                INSERT INTO users (name, surname, email, password)
                VALUES (?, ?, ?, ?)
            ');
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt->execute([$name, $surname, $email, $hashedPassword]); 
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == '23505') { 
                throw new Exception("Email already exists!");
            }
            throw $e; 
        }
    }

}