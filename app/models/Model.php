<?php

namespace Blog\Models;

use PDO;
use PDOException;

class Model
{
    private const DSN = 'mysql:host=database;port=3306;dbname=blog';
    private const MYSQL_USER = 'mysql';
    private const MYSQL_PWD = 'mysql';
    private const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ];

    protected PDO $pdo_connection;

    public function __construct()
    {
        try {
            $this->pdo_connection = new PDO(self::DSN, self::MYSQL_USER, self::MYSQL_PWD, self::PDO_OPTIONS);
        } catch (PDOException $e) {
            echo($e->getMessage());
            exit;
        }
    }
}