<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Admin / Controller
 */
namespace PH7;

use
PH7\Framework\Navigation\Page,
PH7\Framework\Url\Header,
PH7\Framework\Mvc\Router\Uri;

class UserController extends Controller
{

    private $oAdmin, $oAdminModel, $sTitle, $sMsg, $iTotalUsers;

    public function __construct()
    {
        parent::__construct();

        $this->oAdmin = new AdminCore;
        $this->oAdminModel = new AdminModel;

        // Assigns variables for views
        $this->view->designSecurity = new Framework\Layout\Html\Security; // Security Design Class
        $this->view->dateTime = $this->dateTime; // Date Time Class
        $this->view->avatarDesign = new AvatarDesignCore; // For Avatar User
    }

    public function index()
    {
        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'));
    }

    public function browse()
    {
        $this->iTotalUsers = $this->oAdminModel->total();

        $oPage = new Page;
        $this->view->total_pages = $oPage->getTotalPages($this->iTotalUsers, 15);
        $this->view->current_page = $oPage->getCurrentPage();
        $oBrowse = $this->oAdminModel->browse($oPage->getFirstItem(), $oPage->getNbItemsByPage());
        unset($oPage);

        if (empty($oBrowse))
        {
            $this->design->setRedirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'));
            $this->displayPageNotFound(t('No user were found.'));
        }
        else
        {
            // Adding the static files
            $this->design->addCss(PH7_LAYOUT . PH7_TPL . PH7_TPL_NAME . PH7_SH . PH7_CSS, 'browse.css');
            $this->design->addJs(PH7_STATIC . PH7_JS, 'form.js');

            $this->sTitle = t('Browse Users');
            $this->view->page_title = $this->sTitle;
            $this->view->h1_title = $this->sTitle;
            $this->view->h3_title = t('Total Users: %0%', $this->iTotalUsers);

            $this->view->browse = $oBrowse;
            $this->output();
        }
    }

    public function add()
    {
        $this->sTitle = t('Add a User');
        $this->view->page_title = $this->sTitle;
        $this->view->h2_title = $this->sTitle;
        $this->output();
    }

    public function import()
    {
        $this->sTitle = t('Import Users');
        $this->view->page_title = $this->sTitle;
        $this->view->h2_title = $this->sTitle;
        $this->output();
    }

    public function addFakeProfiles()
    {
        $this->sTitle = t('Add Fake Profiles');
        $this->view->page_title = $this->sTitle;
        $this->view->h2_title = $this->sTitle;
        $this->output();
    }

    public function search()
    {
        $this->sTitle = t('Search Users');
        $this->view->page_title = $this->sTitle;
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function result()
    {
        error_reporting(0);

        $iGroupId = $this->httpRequest->get('group_id', 'int');
        $iBan = $this->httpRequest->get('ban', 'int');
        $sWhere = $this->httpRequest->get('where');
        $sWhat = $this->httpRequest->get('what');

        if ($sWhere !== 'all' && $sWhere !== SearchCoreModel::USERNAME && $sWhere !== SearchCoreModel::EMAIL && $sWhere !== SearchCoreModel::FIRST_NAME && $sWhere !== SearchCoreModel::LAST_NAME && $sWhere !== SearchCoreModel::IP)
        {
            \PFBC\Form::setError('form_user_search', 'Invalid argument.');
            Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'search'));
        }
        else
        {
            $this->iTotalUsers = $this->oAdminModel->searchUser($sWhat, $sWhere, $iGroupId, $iBan, true,
                $this->httpRequest->get('order'), $this->httpRequest->get('sort'), null, null);
            $this->view->total_users = $this->iTotalUsers;

            $oPage = new Page;
            $this->view->total_pages = $oPage->getTotalPages($this->iTotalUsers, 15);
            $this->view->current_page = $oPage->getCurrentPage();
            $oSearch = $this->oAdminModel->searchUser($sWhat, $sWhere, $iGroupId, $iBan, false,
                $this->httpRequest->get('order'), $this->httpRequest->get('sort'), $oPage->
                getFirstItem(), $oPage->getNbItemsByPage());
            unset($oPage);

            if (empty($oSearch))
            {
                $this->design->setRedirect(Uri::get(PH7_ADMIN_MOD, 'user', 'search'));
                $this->displayPageNotFound('Empty search result. Please try again with wider or new search parameters.');
            }
            else
            {
                // Adding the static files
                $this->design->addCss(PH7_LAYOUT . PH7_TPL . PH7_TPL_NAME . PH7_SH . PH7_CSS, 'browse.css');
                $this->design->addJs(PH7_STATIC . PH7_JS, 'form.js');

                $this->sTitle = t('Users - Your search returned');
                $this->view->page_title = $this->sTitle;
                $this->view->h1_title = $this->sTitle;
                $this->view->h3_title = nt('%n% User Result!', '%n% Users Result!', $this->iTotalUsers);
                $this->view->browse = $oSearch;
            }

            $this->manualTplInclude('browse.tpl');
            $this->output();
        }
    }

    public function loginUserAs($iId)
    {
        $aSessionData = [
            'login_user_as' => 1,
            'member_id' => $iId,
            'member_email' => $this->oAdminModel->getEmail($iId),
            'member_username' => $this->oAdminModel->getUsername($iId),
            'member_first_name' => $this->oAdminModel->getFirstName($iId),
            'member_sex' => $this->oAdminModel->getSex($iId),
            'member_group_id' => $this->oAdminModel->getGroupId($iId),
            'member_ip' => Framework\Ip\Ip::get(),
            'member_http_user_agent' => $this->browser->getUserAgent(),
            'member_token' => Framework\Util\Various::genRnd()
        ];

        $this->session->set($aSessionData);
        Header::redirect($this->registry->site_url, t('You are now logged in as member: %0%!',
            $this->session->get('member_username')));
    }

    public function logoutUserAs()
    {
        $this->sMsg = t('You are now  logged out in as a member: %0%!', $this->session->
            get('member_username'));

        $aSessionData = [
            'login_user_as',
            'member_id',
            'member_email',
            'member_username',
            'member_first_name',
            'member_sex',
            'member_group_id',
            'member_ip',
            'member_http_user_agent',
            'member_token'
        ];

        $this->session->remove($aSessionData);
        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->
            sMsg);
    }

    public function approve()
    {
        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->_moderateRegistration($this->httpRequest->post('id'), 1));
    }

    public function disapprove()
    {
        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->_moderateRegistration($this->httpRequest->post('id'), 0));
    }

    public function approveAll($iId)
    {
        if(!(new Framework\Security\CSRF\Token)->check('user_action'))
        {
            $this->sMsg = Form::errorTokenMsg();
        }
        elseif (count($this->httpRequest->post('action')) > 0)
        {
            foreach ($this->httpRequest->post('action') as $sAction)
            {
                $iId = (int) explode('_', $sAction)[0];
                $this->sMsg = $this->_moderateRegistration($iId, 1);
            }
        }

        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->sMsg);
    }

    public function disapproveAll($iId)
    {
        if(!(new Framework\Security\CSRF\Token)->check('user_action'))
        {
            $this->sMsg = Form::errorTokenMsg();
        }
        elseif (count($this->httpRequest->post('action')) > 0)
        {
            foreach ($this->httpRequest->post('action') as $sAction)
            {
                $iId = (int) explode('_', $sAction)[0];
                $this->sMsg = $this->_moderateRegistration($iId, 0);
            }
        }

        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->sMsg);
    }

    public function ban()
    {
        $iId = $this->httpRequest->post('id');

        if ($this->oAdminModel->ban($iId, 1))
        {
            $this->oAdmin->clearReadProfileCache($iId);
            $this->sMsg = t('The profile has been banned.');
        }
        else
        {
            $this->sMsg = t('Oops! An error has occurred while banishment the profile.');
        }

        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->sMsg);
    }

    public function unBan()
    {
        $iId = $this->httpRequest->post('id');

        if ($this->oAdminModel->ban($iId, 0))
        {
            $this->oAdmin->clearReadProfileCache($iId);
            $this->sMsg = t('The profile has been unbanned.');
        }
        else
        {
            $this->sMsg = t('Oops! An error has occurred while unban the profile.');
        }

        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->sMsg);
    }

    public function delete()
    {
        $aData = explode('_', $this->httpRequest->post('id'));
        $iId = (int) $aData[0];
        $sUsername = (string) $aData[1];

        $this->oAdmin->delete($iId, $sUsername);
        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), t('The profile has been deleted.'));
    }

    public function banAll()
    {
        if(!(new Framework\Security\CSRF\Token)->check('user_action'))
        {
            $this->sMsg = Form::errorTokenMsg();
        }
        elseif (count($this->httpRequest->post('action')) > 0)
        {
            foreach ($this->httpRequest->post('action') as $sAction)
            {
                $iId = (int) explode('_', $sAction)[0];

                $this->oAdminModel->ban($iId, 1);

                $this->oAdmin->clearReadProfileCache($iId);
            }
            $this->sMsg = t('The profile(s) has been banned.');
        }

        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->sMsg);
    }

    public function unBanAll()
    {
        if(!(new Framework\Security\CSRF\Token)->check('user_action'))
        {
            $this->sMsg = Form::errorTokenMsg();
        }
        elseif (count($this->httpRequest->post('action')) > 0)
        {
            foreach ($this->httpRequest->post('action') as $sAction)
            {
                $iId = (int) explode('_', $sAction)[0];

                $this->oAdminModel->ban($iId, 0);
                $this->oAdmin->clearReadProfileCache($iId);
            }
            $this->sMsg = t('The profile(s) has been unbanned.');
        }

        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->sMsg);
    }

    public function deleteAll()
    {
        if(!(new Framework\Security\CSRF\Token)->check('user_action'))
        {
            $this->sMsg = Form::errorTokenMsg();
        }
        elseif (count($this->httpRequest->post('action')) > 0)
        {
            foreach ($this->httpRequest->post('action') as $sAction)
            {
                $aData = explode('_', $sAction);
                $iId = (int) $aData[0];
                $sUsername = (string) $aData[1];

                $this->oAdmin->delete($iId, $sUsername);
            }
            $this->sMsg = t('The profile(s) has been deleted.');
        }

        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'user', 'browse'), $this->sMsg);
    }

    private function _moderateRegistration($iId, $iStatus)
    {
        if (isset($iId, $iStatus))
        {
            if ($oUser = $this->oAdminModel->readProfile($iId))
            {
                if ($iStatus == 0)
                {
                    // We leave the user in disapproval, after we can ban or delete it.
                    $sSubject = t('Your membership account has been declined');
                    $this->sMsg = t('Sorry, Your membership account has been declined.');
                }
                elseif ($iStatus == 1)
                {
                    // Approve User
                    $this->oAdminModel->approve($oUser->profileId, 1);

                    /** Update the Affiliate Commission **/
                    AffiliateCore::updateJoinCom($oUser->affiliatedId, $this->config, $this->registry);

                    $sSubject = t('Your membership account has been activated');
                    $this->sMsg = t('Congratulations! Your account has been approved by our team of administrators.<br />You can now %0% to meeting new people!',
                        '<a href="' . Uri::get('user', 'main', 'login') . '"><b>' . t('log in') . '</b></a>');
                }
                else
                {
                    // Error...
                    $this->sMsg = null;
                }

                if (!empty($this->sMsg))
                {
                    // Set message
                    $this->view->content = t('Dear %0%,', $oUser->firstName) . '<br />' . $this->sMsg;
                    $this->view->footer = t('You are receiving this mail because we received an application for registration with the email "%0%" has been provided in the form of %site_name% (%site_url%).', $oUser->email) . '<br />' .
                    t('If you think someone has used your email address without your knowledge to create an account on %site_name%, please contact us using our contact form available on our website.');

                    // Send email
                    $sMessageHtml = $this->view->parseMail(PH7_PATH_SYS . 'global/' . PH7_VIEWS . PH7_TPL_NAME . '/mail/sys/core/moderate_registration.tpl', $oUser->email);
                    $aInfo = ['to' => $oUser->email, 'subject' => $sSubject];
                    (new Framework\Mail\Mail)->send($aInfo, $sMessageHtml);

                    $this->oAdmin->clearReadProfileCache($oUser->profileId);

                    $sOutputMsg = t('Done!');
                }
                else
                {
                    $sOutputMsg = t('Error! Bad argument in the url.');
                }
            }
            else
            {
                $sOutputMsg = t('The user is not found!');
            }
        }
        else
        {
            $sOutputMsg = t('Error! Missing argument in the url.');
        }

        return $sOutputMsg;
    }

    public function __destruct()
    {
        unset($this->oAdmin, $this->oAdminModel, $this->sTitle, $this->sMsg, $this->iTotalUsers);
    }

}
