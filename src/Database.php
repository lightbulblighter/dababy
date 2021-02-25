<?php
namespace dababy;

use \dababy\Exception as Issues;

/**
 * DaBaby: DB: General purpose PDO wrapper
 * 
 * Example usage:
 * ```php
 * use \dababy\Database;
 *
 * $db = new Database($username, $password, $repo);
 * $hitlist = $db->run("SELECT `username` FROM `users` WHERE `blurb` LIKE ?", ["%pog%"])->fetchAll();
 * $db->close();
 * ```
 */
class Database
{
    /** @var \PDO $pdo */
    protected $pdo;

    /** @var \PDOStatement $statement */
    protected $statement;

    /**
     * Default fetch style for statements
     */
    const FETCH_STYLE = \PDO::FETCH_ASSOC; // Evaluates to "2"

    /**
     * Default error mode display
     */
    const ERRMODE = \PDO::ERRMODE_SILENT; // $flag ? \PDO::ERRMODE_EXCEPTION : \PDO::ERRMODE_SILENT

    /**
     * Returns a formatted MySQL DSN for PDO
     * 
     * @return string
     */
    private function getDsn($database)
    {
        return "mysql:host=database;port=3306;charset=utf8mb4;dbname=$database";
    }

    /**
     * Class constructor for Database
     * 
     * @param string $username Username for MySQL account
     * @param string $password Password for MySQL account
     * @param string $database MySQL database name
     * @param array $options Optional driver options for PDO connection (default: null)
     * 
     * @throws \dababy\Exception\DatabaseConstructorFailed
     */
    public function __construct($username, $password, $database, $options = null)
    {
        try
        {
            $options = $options ?? [
                \PDO::ATTR_ERRMODE => self::ERRMODE,
                \PDO::ATTR_DEFAULT_FETCH_MODE => self::FETCH_STYLE,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => true, // tie back in if destruction was never called
            ];

            $this->pdo = new \PDO(
                dsn: $this->getDsn($database),
                username: $username,
                password: $password,
                options: $options
            );
        }
        catch (\PDOException $e)
        {
            $message = $e->getMessage();
            error_log($message);
            
            $message = strtolower($message);
            match (true)
            {
                str_contains($message, "could not find driver") => $this->constructorFailed("Could not open a database connection. Is the driver installed or enabled?", $e),
                str_contains($message, "unknown database") => $this->constructorFailed("Could not open a database connection. Does the database exist?", $e),
                default => $this->constructorFailed("Could not open a database connection. Please check your username and password.")
            };
        }
    }

    /**
     * Class destructor for Database
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Throws a DatabaseConstructorFailed exception
     * 
     * @param string $message Error message
     * @param \PDOException $exception Real PDO exception
     * 
     * @throws \dababy\Exception\DatabaseConstructorFailed
     */
    private function constructorFailed($message, $exception)
    {
        throw (new Issues\DatabaseConstructorFailed($message))->setRealException($exception);
    }

    /**
     * Closes the Database connection
     */
    public function close()
    {
        $this->pdo = null;
        $this->statement = null;
    }

    /**
     * Runs a database query
     * 
     * @param string $sql SQL Query to run
     * @param array $args Optional arguments for prepared statement
     * 
     * @return \PDOStatement Statement result
     */
    public function run($sql, $args = null)
    {
        if (!$args)
        {
            $this->statement = $this->pdo->query($sql);
            return $this->statement;
        }

        $this->statement = $this->pdo->prepare($sql);
        $this->statement->execute($args);

        // Check back on this later.
        
        return $this->statement;
    }

    /**
     * Returns the current PDO object
     * 
     * @return \PDO Current PDO object
     */
    public function getPdo()
    {
        return $this->pdo;
    }
}