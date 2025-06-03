<?php


namespace App\Dto;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserDto
{

    public string $fullName;

    public string $username;

    public string $email;

    public string $password;
}