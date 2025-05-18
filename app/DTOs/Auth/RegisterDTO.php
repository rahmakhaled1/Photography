<?php
namespace App\DTOs\Auth;

class RegisterDTO
{
    public string $name;
    public string $email;
    public string $password;
    public string $device_token;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->device_token = $data['device_token'] ?? '';
    }
}
