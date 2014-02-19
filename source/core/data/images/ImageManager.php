<?php
/**
 * User: Lokkie (A.Rusakevich)
 * Date: 18.02.14
 * Time: 11:33
 */

namespace core\data\images;

/**
 * Image processing manager implementation
 *
 * In code works with this class. Contains engine selecting code
 * @package core\data\images
 */
class ImageManager implements iImageProcessor
{
    /**
     * @var iImageProcessor
     */
    protected $processor;

    function __construct($fileName)
    {
        if (class_exists('IMImageProcessor') && class_exists('Imagick')) {
            $this->processor = new IMImageProcessor($fileName);
        } else {
            $this->processor = new GDImageProcessor($fileName);
        }
    }

    /**
     * Resize image
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resize($width = -1, $height = -1)
    {
        $this->processor->resize($width, $height);
        return $this;
    }

    /**
     * Return image content as JPEG
     * @param string $contentType
     * @return string
     */
    public function getContent($contentType = 'jpeg')
    {
        return $this->processor->getContent($contentType);
    }

    /**
     * Outputs image into browser
     * @param string $contentType
     * @return $this
     */
    public function out($contentType = 'jpeg')
    {
        $this->processor->out($contentType);
        return $this;
    }

    /**
     * @return null|resource
     */
    public function getHandle()
    {
        return $this->processor->getHandle();
    }

    /**
     * Creates square center cropped image with specified side length
     * @param int $length
     * @return $this
     */
    public function centerCrop($length)
    {
        if ($this->processor->width() > $this->processor->height()) {
            $this->processor
                ->resize(-1, $length)
                ->crop(round(($this->processor->width() - $length) / 2), 0, $length, $length);
        } else {
            $this->processor
                ->resize($length)
                ->crop(0, round(($this->processor->width() - $length) / 2), $length, $length);
        }
        return $this;
    }

    /**
     * Returns images width
     * @return int
     */
    public function width()
    {
        return $this->processor->width();
    }

    /**
     * Returns images height
     * @return int
     */
    public function height()
    {
        return $this->processor->height();
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
        $this->processor->crop($x, $y, $width, $height);
        return $this;
    }
}