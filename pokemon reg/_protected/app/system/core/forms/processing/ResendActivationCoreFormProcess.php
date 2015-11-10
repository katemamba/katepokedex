<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Core / Form / Processing
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

use PH7\Framework\Mvc\Router\Uri, PH7\Framework\Mail\Mail;

/** For "user" and "affiliate" module **/
class ResendActivationCoreFormProcess extends Form
{

    public function __construct($sTable)
    {
        parent::__construct();

        $sMail = $this->httpRequest->post('mail');

        if ( ! (new ExistsCoreModel)->email($sMail, $sTable) )
        {
            \PFBC\Form::setError('form_resend_activation', t('Oops, this "%0%" is not associated with any %site_name% account. Please, make sure that you entered the e-mail address used in creating your account.', escape(substr($sMail,0,PH7_MAX_EMAIL_LENGTH))));
        }
        else
        {
            if ( !$mHash = (new UserCoreModel)->getHashValidation($sMail) )
            {
                \PFBC\Form::setError('form_resend_activation', t('Oops! Your account is already activated.'));
            }
            else
            {
                $sMod = ($sTable == 'Affiliates') ? 'affiliate' : 'user';

                $sActivateLink = Uri::get($sMod,'account','activate') . PH7_SH . $mHash->email . PH7_SH . $mHash->hashValidation;

                $this->view->content = t('Welcome to %site_name%, %0%!', $mHash->firstName) . '<br />' .
                t('Hello %0% - We are proud to welcome you as a member of %site_name%!', $mHash->firstName) . '<br />' .
                t('Your hash validation is <em>"%0%"</em>.', '<a href="' . $sActivateLink . '">' . $sActivateLink . '</a>') . '<br />' .
                t('Please save the following information for future refenrence:') . '<br /><em>' .
                t('Email: ') . $mHash->email . '.<br />' .
                t('Username: ') . $mHash->username . '.<br />' .
                t('Password: ***** (This field is hidden to protect against theft of your account).') . '.</em>';

                $this->view->footer = t('You are receiving this mail because we received an application for registration with the email "%0%" has been provided in the form of %site_name% (%site_url%).', $mHash->email) . '<br />' .
                t('If you think someone has used your email address without your knowledge to create an account on %site_name%, please contact us using our contact form available on our website.');

                $sMessageHtml = $this->view->parseMail(PH7_PATH_SYS . 'global/' . PH7_VIEWS . PH7_TPL_NAME . '/mail/sys/core/resend_activation.tpl', $mHash->email);

                $aInfo = [
                 'to' => $mHash->email,
                 'subject' => t('Your new password - %site_name%')
                ];

                if ( ! (new Mail)->send($aInfo, $sMessageHtml) )
                   \PFBC\Form::setError('form_resend_activation', Form::errorSendingEmail());
                else
                   \PFBC\Form::setSuccess('form_resend_activation', t('Your hash validation has been emailed to you.'));
            }
        }
    }

}
