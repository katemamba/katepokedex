<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Blog / Config
 */
namespace PH7;
defined('PH7') or die('Restricted access');

class Permission extends PermissionCore
{

    public function __construct()
    {
        parent::__construct();

        // Level for Blogs
        $bAdminAuth = AdminCore::auth();

        if (!$bAdminAuth)
        {
            if (!$this->checkMembership() || ($this->registry->action === 'read' && !$this->group->read_blog_posts))
            {
                $this->paymentRedirect();
            }
        }

        if (!$bAdminAuth && $this->registry->controller === 'AdminController')
        {
            // For security reasons, we do not redirectionnons the user to hide the url of the administrative part.
            Framework\Url\Header::redirect(Framework\Mvc\Router\Uri::get('blog','main','index'), $this->adminSignInMsg(), 'error');
        }
    }

}
