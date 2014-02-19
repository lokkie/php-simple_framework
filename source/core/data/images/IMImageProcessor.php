<?php
/**
 * User: Lokkie (A.Rusakevich)
 * Date: 18.02.14
 * Time: 13:08
 */

namespace core\data\images;

/**
 * Image processor implementation based on Imagick library
 *
 * Not implemented yet
 * @package core\data\images
 * @todo implement this class
 */
class IMImageProcessor implements iImageProcessor
{

    /**
     * @param string $fileName
     */
    function __construct($fileName)
    {
        // TODO: Implement __construct() method.
    }

    /**
     * Resize image
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resize($width = -1, $height = -1)
    {
        // TODO: Implement resize() method.
    }

    /**
     * Return image content as JPEG
     * @param string $contentType
     * @return string
     */
    public function getContent($contentType = 'jpeg')
    {
        // TODO: Implement getContent() method.
    }

    /**
     * Outputs image into browser
     * @param string $contentType
     * @return $this
     */
    public function out($contentType = 'jpeg')
    {
        // TODO: Implement out() method.
    }

    /**
     * @return null|resource
     */
    public function getHandle()
    {
        // TODO: Implement getHandle() method.
    }

    /**
     * Returns images width
     * @return int
     */
    public function width()
    {
        // TODO: Implement width() method.
    }

    /**
     * Returns images height
     * @return int
     */
    public function height()
    {
        // TODO: Implement height() method.
    }

    /**
     * Crop image by provided rectangle
     * @param int $x
     * @param int $y
     * @param int $width if is -1, from $x to end of image
     * @param int $height if is -1, from $y to end of image
     * @return $this
     */
    public function crop($x = 0, $y = 0, $width = -1, $height = -1)
    {
        // TODO: Implement crop() method.
    }
}