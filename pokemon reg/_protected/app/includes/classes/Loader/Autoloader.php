<?php
/**
 * @title            Autoloader Class
 * @desc             Loading classes to include additional.
 *
 * @author           Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright        (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license          GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package          PH7 / App / Include / Class / Loader
 * @version          1.0
 */

namespace PH7\App\Includes\Classes\Loader;
defined('PH7') or exit('Restricted access');

use \PH7\Framework\Registry\Registry;

final class Autoloader
{

    /**
     * We use this class with the singleton pattern.
     */
    use \PH7\Framework\Pattern\Singleton;

    /**
     * We do not put a "__construct" and "__clone" "private" because it is already included in the class \PH7\Framework\Pattern\Base that is included in the \PH7\Framework\Pattern\Singleton class.
     */


    /**
     * Init Autoload Class.
     *
     * @return void
     */
    public function init()
    {
        // Specify the extensions that may be loaded
        spl_autoload_extensions('.php');
        /** Register the loader methods **/
        spl_autoload_register(array(__CLASS__, '_loadController'));
        spl_autoload_register(array(__CLASS__, '_loadClass'));
        spl_autoload_register(array(__CLASS__, '_loadModel'));
        spl_autoload_register(array(__CLASS__, '_loadForm'));
    }

    /**
     * Autoload Controllers.
     *
     * @param string $sClass
     * @return void
     */
    private function _loadController($sClass)
    {
        $sClass = $this->_removeNamespace($sClass);

        // For the Controllers of the modules
        if (is_file(Registry::getInstance()->path_module_controllers . $sClass . '.php'))
            require_once Registry::getInstance()->path_module_controllers . $sClass . '.php';
    }

    /**
     * Autoload Classes.
     *
     * @param string $sClass
     * @return void
     */
    private function _loadClass($sClass)
    {
        $sClass = $this->_removeNamespace($sClass);

        // For the global Classes of the pH7Framework
        if (is_file(PH7_PATH_APP . 'includes/classes/' . $sClass . '.php'))
            require_once PH7_PATH_APP . 'includes/classes/' . $sClass . '.php';

        // For the Core Classes
        if (is_file(PH7_PATH_SYS . 'core/classes/' . $sClass . '.php'))
            require_once PH7_PATH_SYS . 'core/classes/' . $sClass . '.php';

        // For the Classes of the modules
        if (is_file(Registry::getInstance()->path_module_inc . 'class/' . $sClass . '.php'))
            require_once Registry::getInstance()->path_module_inc . 'class/' . $sClass . '.php';

        // For the Core Designs Classes
        if (is_file(PH7_PATH_SYS . 'core/classes/design/' . $sClass . '.php'))
            require_once PH7_PATH_SYS . 'core/classes/design/' . $sClass . '.php';

        // For the Designs Classes of the modules
        if (is_file(Registry::getInstance()->path_module_inc . 'class/design/' . $sClass . '.php'))
            require_once Registry::getInstance()->path_module_inc . 'class/design/' . $sClass . '.php';
    }

    /**
     * Autoload Models.
     *
     * @param string $sClass
     * @return void
     */
    private function _loadModel($sClass)
    {
        $sClass = $this->_removeNamespace($sClass);

        // For the Core Models
        if (is_file(PH7_PATH_SYS . 'core/' . PH7_MODELS . $sClass . '.php'))
            require_once PH7_PATH_SYS . 'core/' . PH7_MODELS . $sClass . '.php';

        // For the Models of the modules
        if (is_file(Registry::getInstance()->path_module_models . $sClass . '.php'))
            require_once Registry::getInstance()->path_module_models . $sClass . '.php';

        // For the Core Designs Models
        if (is_file(PH7_PATH_SYS . 'core/' . PH7_MODELS . 'design/' . $sClass . '.php'))
            require_once PH7_PATH_SYS . 'core/' . PH7_MODELS . 'design/' . $sClass . '.php';

        // For the Core Designs Models of the modules
        /**
         * @internal It is rare that you would need to use a Designs Model Class in your module, so we're not going to load it here.
         */
    }

    /**
     * Autoload Forms.
     *
     * @param string $sClass
     * @return void
     */
    private function _loadForm($sClass)
    {
        $sClass = $this->_removeNamespace($sClass);

        // For the Core Forms
        if (is_file(PH7_PATH_SYS . 'core/' . PH7_FORMS . $sClass . '.php'))
            require_once PH7_PATH_SYS . 'core/' . PH7_FORMS . $sClass . '.php';

        if (is_file(PH7_PATH_SYS . 'core/' . PH7_FORMS . 'processing/' . $sClass . '.php'))
            require_once PH7_PATH_SYS . 'core/' . PH7_FORMS . 'processing/' . $sClass . '.php';

        // For the Forms of the modules
        if (is_file(Registry::getInstance()->path_module_forms . $sClass . '.php'))
            require_once Registry::getInstance()->path_module_forms . $sClass . '.php';

        if (is_file(Registry::getInstance()->path_module_forms . 'processing/' . $sClass . '.php'))
            require_once Registry::getInstance()->path_module_forms . 'processing/' . $sClass . '.php';
    }

    /**
     * Hack to remove the 'PH7' namespace.
     *
     * @param string $sClass
     * @return string
     */
    private function _removeNamespace($sClass)
    {
        return str_replace('PH7\\', '', $sClass);
    }

}
