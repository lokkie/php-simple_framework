<?php
/**
 * User: Lokkie
 * Date: 18.02.14
 * Time: 1:48
 */

namespace core\exceptions;

/**
 * Simple on file not found exception
 * @package core\exceptions
 */
class FileNotFoundException extends \Exception
{
    /**
     * @var string
     */
    private $filePath = "";

    /**
     * @param string $filePath
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    function __construct($filePath, $message = "File not found", $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->filePath = $filePath;
    }

    /**
     * Returns file name, which is not found
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }
}

