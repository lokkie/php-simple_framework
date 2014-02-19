<?php
/**
 * User: Lokkie (A.Rusakevich)
 * Date: 18.02.14
 * Time: 13:05
 */

namespace core\data\images;

/**
 * Image processors interface
 * @package core\data\images
 */
interface iImageProcessor
{
    /**
     * @param string $fileName
     */
    function __construct($fileName);

    /**
     * Resize image
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resize($width = -1, $height = -1);

    /**
     * Return image content as JPEG
     * @param string $contentType
     * @return string
     */
    public function getContent($contentType = 'jpeg');

    /**
     * Outputs image into browser
     * @param string $contentType
     * @return $this
     */
    public function out($contentType = 'jpeg');

    /**
     * @return null|resource
     */
    public function getHandle();

    /**
     * Returns images width
     * @return int
     */
    public function width();

    /**
     * Returns images height
     * @return int
     */
    public function height();

    /**
     * Crop image by provided rectangle
     * @param int $x
     * @param int $y
     * @param int $width if is -1, from $x to end of image
     * @param int $height if is -1, from $y to end of image
     * @return $this
     */
    public function crop($x = 0, $y = 0, $width = -1, $height = -1);

}