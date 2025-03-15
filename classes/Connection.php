<?php
/**
 * Encapsulates a connection to the database 
 * 
 * @author  Arturo Mora-Rioja
 * @version 1.0.0 August 2020
 * @version 1.0.1 December 2024. PSR standard enforced
 *                               Class design extended
*/

require_once 'Config.php';
require_once 'Utils.php';
    
abstract class DB extends Config 
{
    private const ERROR_CONN = 'There was a connection error';
    protected const ERROR_QUERY = 'There was a database querying error: ';

    protected ?PDO $pdo;
    public string $lastErrorMessage = '';

    /**
     * Opens a connection to the database.
     * In case of error, $this->pdo is set to false
     */
    public function __construct() 
    {
        $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DB_NAME . ';charset=utf8';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        try {
            $this->pdo = new PDO($dsn, self::USERNAME, self::PASSWORD, $options); 
        } catch (\PDOException $e) {
            Utils::debug($e);
            $this->disconnect();
            $this->lastErrorMessage = DB::ERROR_CONN;
        }
    }

    /**
     * Closes a connection to the database
     */
    public function disconnect(): void
    {
        $this->pdo = null;
    }
}