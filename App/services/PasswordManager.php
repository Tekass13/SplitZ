
<?php

class PasswordManager {

    public function __construct() {}

    function validatePassword(string $password): bool
    {
        $regex = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';
        return preg_match($regex, $password);
    }
}