<?php
require_once __DIR__ . '/env.php';

class Database {
    private $conn;

    public function conectar() {
        $this->conn = null;
        try {
            $url    = parse_url(getenv('DATABASE_URL'));
            $host   = $url['host'];
            $dbname = ltrim($url['path'], '/');
            $user   = $url['user'];
            $pass   = $url['pass'];
            $dsn    = "pgsql:host={$host};dbname={$dbname};sslmode=require";
            $this->conn = new PDO($dsn, $user, $pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            die("Erro ao conectar com o banco de dados.");
        }
        return $this->conn;
    }
}
