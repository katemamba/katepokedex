<?php
/**
 * @title          User API Ajax Class
 *
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2013-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / User / Asset / Ajax
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

use PH7\Framework\Mvc\Request\Http;

class UserApiAjax
{

    private $_oUser, $_oUserModel, $_mOutput;

    public function __construct()
    {
        $this->_oUser = new UserCore;
        $this->_oUserModel = new UserCoreModel;
        $this->_init();
    }

    public function display()
    {
        return $this->_mOutput;
    }

    private function _init()
    {
        $oHttpRequest = new Http;
        $sParam = $oHttpRequest->post('param');
        $sType = $oHttpRequest->post('type');
        unset($oHttpRequest);

        switch( $sType )
        {
            case 'profile_link':
                $this->_mOutput = $this->_oUser->getProfileLink($sParam);
            break;

            // If we receive another invalid value, we display a message with a HTTP header.
            default:
                Framework\Http\Http::setHeadersByCode(400);
            exit('Bad Request Error!');
        }
    }

    public function __destruct()
    {
        unset($this->_oUser, $this->_oUserModel, $this->_mOutput);
    }

}

echo (new UserApiAjax)->display();
