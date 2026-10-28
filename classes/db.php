<?php

class db {

    private $dbhost = DB_HOST;
    private $dbuser = DB_USER;
    private $dbpass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbtype = DB_TYPE;
    private $conn;
    private $stmt;

    public function __construct(?string $host = null, ?string $user = null, ?string $pass = null, ?string $name = null, ?string $type = null) {
        $args = array('host', 'user', 'pass', 'name', 'type');
        foreach($args as $arg){
            if($$arg !== null){
                $argname = 'db' . $arg;
                $this->$argname = $$arg;
            }
        }
        switch (strtolower($this->dbtype)) {
            case 'mysql':
                $dsn = "mysql:host={$this->dbhost};dbname={$this->dbname}";
                break;

            default:
                throw new InvalidArgumentException('Invalid DB Type');
        }
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        try {
            $this->conn = new PDO($dsn, $this->dbuser, $this->dbpass, $options);
        } catch (PDOException $ex) {
            // Don't pass the PDO message on: it can include connection details
            error_log('DB connection failed: ' . $ex->getMessage());
            throw new RuntimeException('Database unavailable');
        }
    }

    public function query($sql) {
        $this->stmt = $this->conn->prepare($sql);
    }

    public function execute() {
        return $this->stmt->execute();
    }

    public function fetch($fetchType = PDO::FETCH_OBJ) {
        return $this->stmt->fetch($fetchType);
    }

}
