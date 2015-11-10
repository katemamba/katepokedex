<?php
/**
 * @title            Vimeo Class
 *
 * @author           Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright        (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license          GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package          PH7 / Framework / Video / Api
 * @version          1.0
 * @link             http://hizup.com
 */

namespace PH7\Framework\Video\Api;
defined('PH7') or exit('Restricted access');

class Vimeo extends Api implements IApi
{

    const
    API_URL = 'http://vimeo.com/api/v2/video/',
    PLAYER_URL = 'http://player.vimeo.com/video/';

    /**
     * @param string $sUrl
     * @return mixed (string | boolean) Returns the video embed URL if it was found and is valid, FALSE otherwise.
     */
    public function getVideo($sUrl)
    {
        return $this->getEmbedUrl($sUrl);
    }

    /**
     * @param string $sUrl
     * @return mixed (object | boolean) FALSE if unable to open the url, otherwise the this object.
     */
    public function getInfo($sUrl)
    {
        $sDataUrl = static::API_URL . $this->getVideoId($sUrl) . '.json';
        if ($oData = $this->getData($sDataUrl))
        {
            $this->oData = $oData[0];
            return $this;
        }

        return false;
    }

    public function getMeta($sUrl, $sMedia, $iWidth, $iHeight)
    {

        if ($sMedia == 'preview')
        {
            // First load the video information.
            $this->getInfo($sUrl);
            // Then retrieve the thumbnail.
            return $this->oData->thumbnail_medium;
        }
        else
        {
            $sParam = ($this->bAutoplay) ? '?autoplay=1' : '';
            return '<iframe src="' . $this->getEmbedUrl($sUrl) . $sParam . '&amp;title=0&amp;byline=0&amp;portrait=0" width="' . $iWidth . '" height="' . $iHeight . '" frameborder="0"></iframe>';
        }
    }

    /**
     * @param string $sUrl
     * @return mixed (integer | boolean) Returns the ID of the video if it was found, FALSE otherwise.
     */
    public function getVideoId($sUrl)
    {
        preg_match('#/(\d+)($|/)#i', $sUrl, $aMatch);
        return (!empty($aMatch[1])) ? $aMatch[1] : false;
    }

}
