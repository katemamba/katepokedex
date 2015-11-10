<?php
/**
 * @title            Image Class
 * @desc             Class is used to create/manipulate images using GD library.
 *
 * @author           Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright        (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license          GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package          PH7 / Framework / Image
 * @version          1.1
 * @link             http://hizup.com
 * @linkGD           http://php.net/manual/book.image.php
 */

namespace PH7\Framework\Image;
defined('PH7') or exit('Restricted access');

use PH7\Framework\File\File;

class Image
{

    /*** Alias ***/
    const JPG = IMAGETYPE_JPEG, PNG = IMAGETYPE_PNG, GIF = IMAGETYPE_GIF;

    private $sFile, $sType, $aInfo, $rImage, $iWidth, $iHeight, $iMaxWidth, $iMaxHeight, $iQuality = 100, $iCompression = 4;

    /**
     * @constructor
     * @param string $sFile
     * @param integer $iMaxWidth Default value 3000.
     * @param integer $iMaxHeight Default value 3000.
     */
    public function __construct($sFile, $iMaxWidth = 3000, $iMaxHeight = 3000)
    {
        $this->sFile = $sFile;
        $this->iMaxWidth = $iMaxWidth;
        $this->iMaxHeight = $iMaxHeight;
    }

    /**
     * @desc Image Validate.
     * @return boolean
     * @throws \PH7\Framework\Error\CException\PH7BadMethodCallException If the image file is not found.
     */
    public function validate()
    {
        if (!is_file($this->sFile))
        {
            if (isDebug())
                throw new \PH7\Framework\Error\CException\PH7BadMethodCallException('Image file not found: The image file \'' . $this->sFile . '\' could not be found.');
            else
                return false;
        }
        else
        {
            $this->aInfo = getimagesize($this->sFile);

            switch ($this->aInfo[2])
            {
                // JPG
                case self::JPG:
                    $this->rImage = imagecreatefromjpeg($this->sFile);
                    $this->sType = 'jpg';
                    break;

                // PNG
                case self::PNG:
                    $this->rImage = imagecreatefrompng($this->sFile);
                    $this->sType = 'png';
                    break;

                // GIF
                case self::GIF:
                    $this->rImage = imagecreatefromgif($this->sFile);
                    $this->sType = 'gif';
                    break;

                // Invalid Zone
                default:
                    return false; // File type incompatible. Please save the image in .jpg, .png or .gif
            }

            $this->iWidth = imagesx($this->rImage);
            $this->iHeight = imagesy($this->rImage);

            // Automatic resizing if the image is too large
            if ($this->iWidth > $this->iMaxWidth OR $this->iHeight > $this->iMaxHeight)
                $this->dynamicResize($this->iMaxWidth, $this->iMaxHeight);

            return true;
        }
    }

    /**
     * @desc Image Quality.
     * @param integer $iQ Devault value 100.
     * @return object $this
     */
    public function quality($iQ = 100)
    {
        $this->iQuality = $iQ;
        return $this;
    }

    /**
     * @desc Image Compression.
     * @param integer $iC Devault value 4.
     * @return object $this
     */
    public function compression($iC = 4)
    {
        $this->iCompression = $iC;
        return $this;
    }

    /**
     * @desc Resize
     * @param integer $iX Default value null
     * @param integer $iY Default value null
     * @return object $this
     */
    public function resize($iX = null, $iY = null)
    {
        // Width not given
        if (!$iX)
        {
            $iX = $this->iWidth * ($iY / $this->iHeight);
        }
        // Height not given
        elseif (!$iY)
        {
            $iY = $this->iHeight * ($iX / $this->iWidth);
        }

        $rTmp = imagecreatetruecolor($iX, $iY);
        imagecopyresampled($rTmp, $this->rImage, 0, 0, 0, 0, $iX, $iY, $this->iWidth, $this->iHeight);
        $this->rImage =& $rTmp;

        $this->iWidth = $iX;
        $this->iHeight = $iY;

        return $this;
    }

    /**
     * @desc Crop.
     * @param integer $iX Default value 0.
     * @param integer $iY Default value 0.
     * @param integer $iWidth Default valie 1.
     * @param integer $iHeight Default value 1.
     * @return object $this
     */
    public function crop($iX = 0, $iY = 0, $iWidth = 1, $iHeight = 1)
    {

        $rTmp = imagecreatetruecolor($iWidth, $iHeight);
        imagecopyresampled($rTmp, $this->rImage, 0, 0, $iX, $iY, $iWidth, $iHeight, $iWidth, $iHeight);
        $this->rImage =& $rTmp;

        $this->iWidth = $iWidth;
        $this->iHeight = $iHeight;

        return $this;
    }

    /**
     * @desc Dynamic Resize.
     * @param integer $iNewWidth
     * @param integer $iNewHeight
     * @return object $this
     */
    public function dynamicResize($iNewWidth, $iNewHeight)
    {
        // Taller image
        if ($iNewHeight > $iNewWidth OR ($iNewHeight == $iNewWidth AND $this->iHeight < $this->iWidth))
        {
            $this->resize(NULL, $iNewHeight);

            $iW = ($iNewWidth - $this->iWidth) / -2;
            $this->crop($iW, 0, $iNewWidth, $iNewHeight);
        }
        // Wider image
        else
        {
            $this->resize($iNewWidth, NULL);

            $iY = ($iNewHeight - $this->iHeight) / -2;
            $this->crop(0, $iY, $iNewWidth, $iNewHeight);
        }

        $this->iWidth = $iNewWidth;
        $this->iHeight = $iNewHeight;

        return $this;
    }

    /**
     * @desc Square.
     * @param integer $iSize
     * @see \PH7\Framework\Image\Image::dynamicResize() The method that is returned by this method.
     * @return object $this
     */
    public function square($iSize)
    {
        return $this->dynamicResize($iSize, $iSize);
    }

    /**
     * @desc Zone Crop.
     * @param integer $iWidth
     * @param integer $iHeight
     * @param string $sZone Default value is center.
     * @see \PH7\Framework\Image\Image::crop() The method that is returned by this method.
     * @return object $this
     * @throws \PH7\Framework\Error\CException\PH7InvalidArgumentException If the image crop is invalid.
     */
    public function zoneCrop($iWidth, $iHeight, $sZone = 'center')
    {

        switch ($sZone)
        {
            // Center
            case 'center':
                $iX = ($iWidth - $this->iWidth) / -2;
                $iY = ($iHeight - $this->iHeight) / -2;
                break;

            // Top Left
            case 'top-left':
                $iX = 0;
                $iY = 0;
                break;

            // Top
            case 'top':
                $iX = ($this->iWidth - $iWidth) / 2;
                $iY = 0;
                break;

            // Top Right
            case 'top-right':
                $iX = $this->iWidth - $iWidth;
                $iY = 0;
                break;

            // Right
            case 'right':
                $iX = $this->iWidth - $iWidth;
                $iY = ($this->iHeight - $iHeight) / 2;
                break;

            // Bottom Right
            case 'bottom-right':
                $iX = $this->iWidth - $iWidth;
                $iY = $this->iHeight - $iHeight;
                break;

            // Bottom
            case 'bottom':
                $iX = ($this->iWidth - $iWidth) / 2;
                $iY = $this->iHeight - $iHeight;
                break;

            // Bottom Left
            case 'bottom-left':
                $iX = 0;
                $iY = $this->iHeight - $iHeight;
                break;

            // Left
            case 'left':
                $iX = 0;
                $iY = ($this->iHeight - $iHeight) / 2;
                break;

            // Invalid Zone
            default:
                throw new \PH7\Framework\Error\CException\PH7InvalidArgumentException('Invalid image crop zone ' . $sZone . ' given for image helper zoneCrop().');
        }

        return $this->crop($iX, $iY, $iWidth, $iHeight);
    }

    /**
     * @desc Rotate.
     * @param integer $iDeg Default value 0.
     * @param integer $iBg Default value 0.
     * @return object $this
     */
    public function rotate($iDeg = 0, $iBg = 0)
    {
        $this->rImage = imagerotate($this->rImage, $iDeg, $iBg);

        return $this;
    }

    /**
     * @desc Create a Watermark text.
     * @param string $sText Text of watermark.
     * @param integer $iSize The size of text. Between 0 to 5.
     * @return object $this
     */
     public function watermarkText($sText, $iSize)
     {
         $iWidthText = $this->iWidth-imagefontwidth($iSize)*mb_strlen($sText)-3;
         $iHeightText = $this->iHeight-imagefontheight($iSize)-3;

         $rWhite = imagecolorallocate($this->rImage, 255, 255, 255);
         $rBlack = imagecolorallocate($this->rImage, 0, 0, 0);
         $rGray = imagecolorallocate($this->rImage, 127, 127, 127);

         if ($iWidthText > 0 && $iHeightText > 0)
         {
             if (imagecolorat($this->rImage, $iWidthText, $iHeightText) > $rGray) $rColor = $rBlack;
                 if (imagecolorat($this->rImage, $iWidthText, $iHeightText) < $rGray) $rColor = $rWhite;
         }
         else
         {
             $rColor = $rWhite;
         }

         imagestring($this->rImage, $iSize, $iWidthText-1, $iHeightText-1, $sText, $rWhite-$rColor);
         imagestring($this->rImage, $iSize, $iWidthText+1, $iHeightText+1, $sText, $rWhite-$rColor);
         imagestring($this->rImage, $iSize, $iWidthText-1, $iHeightText+1, $sText, $rWhite-$rColor);
         imagestring($this->rImage, $iSize, $iWidthText+1, $iHeightText-1, $sText, $rWhite-$rColor);
         imagestring($this->rImage, $iSize, $iWidthText-1, $iHeightText, $sText, $rWhite-$rColor);
         imagestring($this->rImage, $iSize, $iWidthText+1, $iHeightText, $sText, $rWhite-$rColor);
         imagestring($this->rImage, $iSize, $iWidthText, $iHeightText-1, $sText, $rWhite-$rColor);
         imagestring($this->rImage, $iSize, $iWidthText, $iHeightText+1, $sText, $rWhite-$rColor);
         imagestring($this->rImage, $iSize, $iWidthText, $iHeightText, $sText, $rColor);

         return $this;
     }

    /**
     * @desc Save Image.
     * @param string $sFile
     * @return object $this
     * @throws \PH7\Framework\Error\CException\PH7InvalidArgumentException If the image format is invalid.
     */
    public function save($sFile)
    {
        switch ($this->sType)
        {
            // JPG
            case 'jpg':
                imagejpeg($this->rImage, $sFile, $this->iQuality);
                break;

            // PNG
            case 'png':
                imagepng($this->rImage, $sFile, $this->iCompression);
                break;

            // GIF
            case 'gif':
                imagegif($this->rImage, $sFile, $this->iQuality);
                break;

            // Invalid Zone
            default:
                throw new \PH7\Framework\Error\CException\PH7InvalidArgumentException('Invalid format Image in method ' . __METHOD__ . ' of class ' . __CLASS__);
        }

        return $this;
    }

    /**
     * @desc Show Image.
     * @return object $this
     * @throws \PH7\Framework\Error\CException\PH7InvalidArgumentException If the image format is invalid.
     */
    public function show()
    {
        switch ($this->sType)
        {
            // JPG
            case 'jpg':
                header('Content-type: image/jpeg');
                imagejpeg($this->rImage, null, $this->iQuality);
                break;

            // GIF
            case 'gif':
                header('Content-type: image/gif');
                imagegif($this->rImage, null, $this->iQuality);
                break;

            // PNG
            case 'png':
                header('Content-type: image/png');
                imagepng($this->rImage, null, $this->iCompression);
                break;

            // Invalid Zone
            default:
                throw new \PH7\Framework\Error\CException\PH7InvalidArgumentException('Invalid format image in method ' . __METHOD__ . ' of class ' . __CLASS__);
        }

        return $this;
    }

    /**
     * @desc Get File Name.
     * @return string
     */
    public function getFileName()
    {
        return $this->rImage;
    }

    /**
     * @desc Get image extension.
     * @return string The extension of the image without the dot.
     */
    public function getExt()
    {
        return $this->sType;
    }

    /**
     * @desc Remove the attributes, temporary file and memory resources.
     */
    public function __destruct()
    {
        // Remove the temporary image
        (new File)->deleteFile($this->sFile);

        // Free the memory associated with the image
        @imagedestroy($this->rImage);

        unset(
            $this->sFile,
            $this->sType,
            $this->aInfo,
            $this->rImage,
            $this->iWidth,
            $this->iHeight,
            $this->iMaxWidth,
            $this->iMaxHeight,
            $this->iQuality,
            $this->iCompression
        );
    }

}
