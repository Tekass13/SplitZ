<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

abstract class AbstractManager
{
    protected PDO $db;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $this->db = new PDO(
            'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD']
        );
    }
}
