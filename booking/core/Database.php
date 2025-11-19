<?php
/**
 * Database Connection Class
 * PDO-based database wrapper with connection pooling
 *
 * @package ConferenceBooking
 * @version 1.0.0
 */

class Database {
    private static $instance = null;
    private $connection;
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset = 'utf8mb4';

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        require_once __DIR__ . '/../config/database.php';

        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;

        $this->connect();
    }

    /**
     * Get singleton instance
     *
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish database connection
     */
    private function connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true,
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get PDO connection
     *
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Execute a query and return all results
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute a query and return single row
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array|null
     */
    public function queryOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute an insert/update/delete query
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return int Number of affected rows
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Execute failed: " . $e->getMessage());
        }
    }

    /**
     * Get last insert ID
     *
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}

    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
