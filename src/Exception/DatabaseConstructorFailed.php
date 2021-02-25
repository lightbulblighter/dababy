<?php
namespace dababy\Exception;

use ParagonIE\Corner\CornerTrait;

/**
 * Exception for the Database constructor
 * 
 * @package Application
 * @subpackage Exception
 */
class DatabaseConstructorFailed extends \RuntimeException implements ExceptionInterface
{
    use CornerTrait;

    /** @var \PDOException|null $exception */
    private $exception = null;

    /**
     * Sets the DatabaseConstructorFailed exception
     * 
     * @param \PDOException $ex Exception
     * @return DatabaseConstructorFailed
     */
    public function setRealException(PDOException $ex): self
    {
        $this->exception = $ex;
        return $this;
    }

    /**
     * Gets the DatabaseConstructorFailed exception
     * 
     * @return \PDOException|null
     */
    public function getRealException()
    {
        return $this->exception;
    }
}