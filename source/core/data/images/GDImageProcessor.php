<?php
/**
 * User: Lokkie (A.Rusakevich)
 * Date: 18.02.14
 * Time: 11:34
 */

namespace core\data\images;

/**
 * Image processor implementation based on GD library
 * @package core\data\images
 */
class GDImageProcessor implements iImageProcessor
{
    /**
     * @var resource|null
     */
    protected $handle = null;
    /**
     * @var int
     */
    protected $width = 0;
    /**
     * @var int
     */
    protected $height = 0;

    /**
     * @param string $fileName
     */
    function __construct($fileName)
    {
        $this->handle = imagecreatefromstring(file_get_contents($fileName));
        $this->height = imagesy($this->handle);
        $this->width = imagesx($this->handle);
    }

    /**
     * Resize image
     * @param int $width
     * @param int $height
     * @return $this
     */
    public function resize($width = -1, $height = -1)
    {
        if ($width != -1 || $height != -1) {
            if ($width == -1) {
                $width = round($height * $this->width / $this->height);
            } else if ($height == -1) {
                $height = round($width * $this->height / $this->width);
            };
            $tmpImage = imagecreatetruecolor($width, $height);
            imagecopyresampled($tmpImage, $this->handle, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
            $this->handle = $tmpImage;
            $this->width = $width;
            $this->height = $height;
        }
        return $this;
    }

    /**
     * Return image content as JPEG
     * @param string $contentType
     * @return string
     */
    public function getContent($contentType = 'jpeg')
    {
        ob_start();
        call_user_func('image' . $contentType, $this->handle);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * Outputs image into browser
     * @param string $contentType
     * @return $this
     */
    public function out($contentType = 'jpeg')
    {
        if (!in_array($contentType, array('gif', 'png', 'jpeg', 'gd', 'gd2')))
            header('Content-Type: image/' . $contentType);
        call_user_func('image' . $contentType, $this->handle);
        return $this;
    }

    /**
     * @return null|resource
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Returns images width
     * @return int
     */
    public function width()
    {
        return $this->width;
    }

    /**
     * Returns images height
     * @return int
     */
    public function height()
    {
        return $this->height;
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
        if ($x < 0 || $x > $this->width) {
            $x = 0;
        }
        if ($y < 0 || $y > $this->height) {
            $y = 0;
        }
        if ($height == -1) {
            $height = $this->height - $y;
        }
        if ($width == -1) {
            $width = $this->width - $x;
        }
        $tmpImage = imagecreatetruecolor($width, $height);
        imagecopy($tmpImage, $this->handle, 0, 0, $x, $y, $width, $height, $width, $height);
        $this->handle = $tmpImage;
        $this->width = $width;
        $this->height = $height;
        return $this;
    }
}