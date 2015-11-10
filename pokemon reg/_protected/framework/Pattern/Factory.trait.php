<?php
/**
 * @title            Factory Trait
 *
 * @author           Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright        (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license          GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package          PH7 / Framework / Pattern
 * @version          1.0
 */

namespace PH7\Framework\Pattern;
defined('PH7') or exit('Restricted access');

trait Factory
{

    use Base;

    /**
     * Loading a class.
     *
     * @access public
     * @static
     * @return object Return the instance of the class.
     * @throws \PH7\Framework\Error\CException\PH7RuntimeException If the class is not found or if it has not been defined.
     */
    public static function load()
    {
        /**
         * PHP 5.5
         *
        $sClass = static::class;
         */
        $sClass = get_called_class();//remove it for static::class
        $aArgs = func_get_args();

        if (class_exists($sClass))
            return (new \ReflectionClass($sClass))->newInstanceArgs($aArgs);
        else
            throw new \PH7\Framework\Error\CException\PH7RuntimeException('The "' . $sClass . '" was not found or is not defined.');
    }

}

