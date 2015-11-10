<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Core / Class
 */
namespace PH7;

use PH7\Framework\Mvc\Model\DbConfig, PH7\Framework\Mvc\Router\Uri;

/**
 * @abstract
 */
abstract class RegistrationCore extends Core
{

    private $iActiveType;

    public function __construct()
    {
       parent::__construct();

       $this->iActiveType = DbConfig::getSetting('userActivationType');
    }

    /**
     * Send an email.
     *
     * @param array $aInfo
     * @param boolean $bIsUniversalLogin Default: FALSE
     * @return object this
     */
    public function sendMail(array $aInfo, $bIsUniversalLogin = false)
    {
        switch($this->iActiveType)
        {
            case 1:
                $sEmailMsg = t('Please %0% now to meet new people!', '<a href="' . Uri::get('user','main','login') . '"><b>'.t('log in').'</b></a>');
            break;

            case 2:
                /** We place the text outside of Uri::get() otherwise special characters will be deleted and the parameters passed in the url will be unusable thereafter. **/
                $sActivateLink = Uri::get('user','account','activate') . PH7_SH . $aInfo['email'] . PH7_SH . $aInfo['hash_validation'];
                $sEmailMsg = t('Activation link: %0%.', '<a href="' . $sActivateLink . '">' . $sActivateLink . '</a>');
            break;

            case 3:
                $sEmailMsg = t('Caution! Your account is not activated yet. You will receive an email of any decision.');
            break;

            default:
                $sEmailMsg = '';
        }

        $sPwdMsg = ($bIsUniversalLogin) ? t('Password: %0% (please change it next time you login).', $aInfo['password']) : t('Password: ****** (This field is hidden to protect against theft of your account. If you have forgotten your password, please request a new one <a href="%0%">here</a>).', Uri::get('user','main','forgot'));

        $this->view->content = t('Welcome to %site_name%, %0%!', $aInfo['first_name']) . '<br />' .
        t('Hi %0%! We are proud to welcome you as a member of %site_name%!', $aInfo['first_name']) . '<br />' .
        $sEmailMsg . '<br />' .
        '<br /><span style="text-decoration:underline">' . t('Please save the following information for future refenrence:') . '</span><br /><em>' .
        t('Email: %0%.', $aInfo['email']) . '<br />' .
        t('Username: %0%.', $aInfo['username']) . '<br />' .
        $sPwdMsg . '</em>';
        $this->view->footer = t('You are receiving this mail because we received an application for registration with the email "%0%" has been provided in the form of %site_name% (%site_url%).', $aInfo['email']) . '<br />' .
        t('If you think someone has used your email address without your knowledge to create an account on %site_name%, please contact us using our contact form available on our website.');

        $sTplName = (defined('PH7_TPL_NAME')) ? PH7_TPL_NAME : PH7_DEFAULT_THEME;
        $sMsgHtml = $this->view->parseMail(PH7_PATH_SYS . 'global/' . PH7_VIEWS . $sTplName . '/mail/sys/mod/user/account_registration.tpl', $aInfo['email']);

        $aMailInfo = [
          'to' => $aInfo['email'],
          'subject' => t('Hello %0%, Welcome to %site_name%!', $aInfo['first_name'])
        ];

        (new Framework\Mail\Mail)->send($aMailInfo, $sMsgHtml);

        return $this;
    }

    /**
     * Get the registration message.
     *
     * @return string The message.
     */
    public function getMsg()
    {
        switch($this->iActiveType)
        {
            case 1:
                $sMsg = t('Login now!');
            break;

            case 2:
                $sMsg = t('Please activate your account by clicking the activation link you received by email. If you can not find the email, please look in your SPAM FOLDER and mark as not spam.');
             break;

            case 3:
                $sMsg = t('Your account must be approved by an administrator. You will receive an email of any decision.');
            break;

            default:
                $sMsg = '';
        }

        return $sMsg;
    }

}
