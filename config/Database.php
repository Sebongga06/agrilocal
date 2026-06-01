<?php
class Database {
    private $host = 'localhost';
    private $db   = 'u367097290_db_agrilocal';  
    private $user = 'u367097290_root';     
    
    //u367097290_

    //y*CGNqgLfV3Ug7C
    private $pass = 'y*CGNqgLfV3Ug7C';                 
    private $charset = 'utf8mb4';
    private $pdo;

    public function getConnection() {
        if ($this->pdo === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        }
        return $this->pdo;
    }
}
