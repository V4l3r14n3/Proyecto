<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // si usas Composer

use MongoDB\Client;

class Conexion {
    private $cliente;
    private $db;

    public function __construct() {
        $this->cliente = new Client("mongodb://localhost:27017/Proyecto");
        $this->db = $this->cliente->voluntariadosDB;
    
    }

    public function getDB() {
        return $this->db;
    }
}
