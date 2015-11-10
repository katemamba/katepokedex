<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Affiliate / Inc / Class
 */
namespace PH7;
use PH7\Framework\Mvc\Model\DbConfig, PH7\Framework\Mvc\Router\Uri;

class Registration extends RegistrationCore
{

    public function __construct()
    {
       parent::__construct();

       $this->iActiveType = DbConfig::getSetting('affActivationType');
    }

    public function sendMail(array $aInfo, $bIsUniversalLogin = false)
    {
        switch($this->iActiveType)
        {
            case 1:
              $sEmailMsg = t('Please %0% now to make money!', '<a href="' . Uri::get('affiliate','home','login') . '"><b>'.t('log in').'</b></a>');
            break;

            case 2:
              /** We place the text outside of Uri::get() otherwise special characters will be deleted and the parameters passed in the url will be unusable thereafter. **/
              $sActivateLink = Uri::get('affiliate','account','activate') . PH7_SH . $aInfo['email'] . PH7_SH . $aInfo['hash_validation'];
              $sEmailMsg = t('Activation link: %0%.', '<a href="' . $sActivateLink . '">' . $sActivateLink . '</a>');
            break;

            case 3:
              $sEmailMsg = t('Caution! Your account is not activated yet. You will receive an email of any decision.');
            break;

            default:
              $sEmailMsg = '';
        }

        $this->view->content = t('Dear %0% %1%, welcome to affiliate platform %site_name%!', $aInfo['last_name'], $aInfo['first_name']) . '<br />' .
        t('Hello %0%! We are proud to welcome you as an affiliate on our %site_name%!', $aInfo['first_name']) . '<br />' .
        $sEmailMsg .
        '<br /><span style="text-decoration:underline">' . t('Please save the following information for future refenrence:') . '</span><br /><em>' .
        t('Email: %0%.', $aInfo['email']) . '<br />' .
        t('Username: %0%.', $aInfo['username']) . '<br />' .
        t('Password: ****** (This field is hidden to protect against theft of your account. If you have forgotten your password, please request a new one <a href="%0%">here</a>).', Uri::get('affiliate','home','forgot')) . '</em>';
        $this->view->footer = t('You are receiving this mail because we received an application for registration with the email "%0%" has been provided in the form of %site_name% (%site_url%).', $aInfo['email']) . '<br />' .
        t('If you think someone has used your email address without your knowledge to create an account on %site_name%, please contact us using our contact form available on our website.');

        $sMsgHtml = $this->view->parseMail(PH7_PATH_SYS . 'global/' . PH7_VIEWS . PH7_TPL_NAME . '/mail/sys/mod/affiliate/registration.tpl', $aInfo['email']);

        $aMailInfo = [
          'to' => $aInfo['email'],
          'subject' => t('Dear %0% %1%, Welcome to our affiliate platform!', $aInfo['last_name'], $aInfo['first_name'])
        ];

        (new Framework\Mail\Mail)->send($aMailInfo, $sMsgHtml);

        return $this;
    }

}
