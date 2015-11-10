<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Note / Config
 */
namespace PH7;
defined('PH7') or die('Restricted access');

class Permission extends PermissionCore
{

    public function __construct()
    {
        parent::__construct();

        // Level for Notes
        $bAdminAuth = AdminCore::auth();

        if(!UserCore::auth() && ($this->registry->action === 'add' || $this->registry->action === 'edit' || $this->registry->action === 'delete'))
        {
            $this->signUpRedirect();
        }

        if (!$bAdminAuth)
        {
            if (!$this->checkMembership() || ($this->registry->action === 'read' && !$this->group->read_notes))
            {
                $this->paymentRedirect();
            }
            elseif ($this->registry->action === 'add' && !$this->group->write_notes)
            {
                $this->paymentRedirect();
            }
        }

        if(!$bAdminAuth && $this->registry->controller === 'AdminController')
        {
            // For security reasons, we do not redirectionnons the user to hide the url of the administrative part.
            Framework\Url\Header::redirect(Framework\Mvc\Router\Uri::get('blog','main','index'), $this->adminSignInMsg(), 'error');
        }
    }

}
