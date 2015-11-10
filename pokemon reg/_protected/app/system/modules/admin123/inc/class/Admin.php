<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Admin / Inc / Class
 */
namespace PH7;

use PH7\Framework\Session\Session, PH7\Framework\Url\Header, PH7\Framework\Mvc\Router\Uri;

class Admin extends AdminCore
{

    /**
     * Logout function for admins.
     *
     * @return void
     */
    public function logout()
    {
        (new Session)->destroy();

        Header::redirect(Uri::get(PH7_ADMIN_MOD,'main','login'), t('You have logged out!'));
    }

    /**
     * Delete Admin.
     *
     * @param integer $iProfileId
     * @param string $sUsername
     * @return void
     */
    public function delete($iProfileId, $sUsername)
    {
        $iProfileId = (int) $iProfileId;

        if($iProfileId === 1) exit('You cannot delete the Root Administrator!');
        (new AdminModel)->delete($iProfileId, $sUsername);
    }

}
