<?php
namespace App\Modele;

class Utilisateur
{
    public $name;
    public $email;
    public $password;
    public $activation_token;

    public function __construct($data = [])
    {
        // Expecting fields: name, email, password
        $this->name = isset($data['name']) ? trim($data['name']) : (isset($data['nom']) ? trim($data['nom']) : '');
        $this->email = isset($data['email']) ? trim($data['email']) : '';
        $this->password = isset($data['password']) ? $data['password'] : (isset($data['motdepasse']) ? $data['motdepasse'] : '');
        $this->activation_token = bin2hex(random_bytes(16));
    }

    public function getActivation_token()
    {
        return $this->activation_token;
    }

    public function getEmail()
    {
        return $this->email;
    }
}

?>