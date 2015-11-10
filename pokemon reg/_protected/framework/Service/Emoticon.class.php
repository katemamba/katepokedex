<?php
/**
 * @title            Emoticon Class
 * @desc             Emoticon Service.
 *
 * @author           Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright        (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license          GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package          PH7 / Framework / Service
 * @version          1.1
 */

namespace PH7\Framework\Service;
defined('PH7') or exit('Restricted access');

/**
 * @class Abstract Class
 */
abstract class Emoticon
{

    const DIR = 'smile/', EXT = '.gif';

    /**
     * Private constructor to prevent instantiation of class since it is a private class.
     *
     * @access private
     */
    private function __construct() {}

    /**
     * Gets the list of emoticons.
     *
     * @access protected
     * @static
     * @return array
     */
    protected static function gets()
    {
        return include PH7_PATH_APP_CONFIG . 'emoticon.php';
    }

    /**
     * Gets the path of emoticon.
     *
     * @access protected
     * @static
     * @param string $sName
     * @return Emoticon path.
     */
    protected static function getPath($sName)
    {
        return PH7_PATH_STATIC . PH7_IMG . static::DIR . $sName . static::EXT;
    }

    /**
     * Gets the URL of emoticon.
     *
     * @access protected
     * @static
     * @param string $sName
     * @return Emoticon URL.
     */
    protected static function getUrl($sName)
    {
        return PH7_URL_STATIC . PH7_IMG . static::DIR . $sName . static::EXT;
    }

    /**
     * Gets the name of emoticon.
     *
     * @access protected
     * @static
     * @param array $aVal
     * @return Emoticon name.
     */
    protected static function getName($aVal)
    {
        return $aVal[1];
    }

    /**
     * Gets the emoticon code.
     *
     * @access protected
     * @static
     * @param array $aVal
     * @return Emoticon code.
     */
    protected static function getCode($aVal)
    {
        return $aVal[0];
    }

    /**
     * Block cloning.
     *
     * @access private
     */
    private function __clone() {}

}
