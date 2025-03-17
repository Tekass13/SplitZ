<?php

abstract class AbstractManager
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = new PDO('mysql:host=localhost;dbname=splitz_bdd', 'root', '');
    }
}
