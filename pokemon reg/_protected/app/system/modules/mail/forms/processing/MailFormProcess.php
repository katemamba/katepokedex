<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Mail / Form / Processing
 */
namespace PH7;
defined('PH7') or die('Restricted access');

use
PH7\Framework\Mvc\Model\DbConfig,
PH7\Framework\Mail\Mail,
PH7\Framework\Mvc\Request\Http,
PH7\Framework\Mvc\Router\Uri,
PH7\Framework\Url\Header;

class MailFormProcess extends Form
{

    public function __construct()
    {
        parent::__construct();

        $oUserModel = new UserCoreModel;
        $oMailModel = new MailModel;

        $bIsAdmin = (AdminCore::auth() && !UserCore::auth() && !$this->session->exists('login_user_as'));
        $sMessage = $this->httpRequest->post('message', Http::ONLY_XSS_CLEAN);
        $sCurrentTime = $this->dateTime->get()->dateTime('Y-m-d H:i:s');
        $iTimeDelay = (int) DbConfig::getSetting('timeDelaySendMail');
        $sRecipient = $this->httpRequest->post('recipient');
        $iRecipientId = $oUserModel->getId(null, $sRecipient);
        $iSenderId = (int) ($bIsAdmin ? PH7_ADMIN_ID : $this->session->get('member_id'));

        if ($iSenderId == $iRecipientId)
        {
            \PFBC\Form::setError('form_compose_mail', t('Oops! You can not send a message to yourself.'));
        }
        elseif ($sRecipient == PH7_ADMIN_USERNAME)
        {
            \PFBC\Form::setError('form_compose_mail', t('Oops! You cannot reply to administrator! If you want to contact us, please use our <a href="%0%">contact form</a>.', Uri::get('contact', 'contact', 'index')));
        }
        elseif ( ! (new ExistsCoreModel)->id($iRecipientId, 'Members') )
        {
            \PFBC\Form::setError('form_compose_mail', t('Oops! The username "%0%" does not exist.', escape(substr($this->httpRequest->post('recipient'),0, PH7_MAX_USERNAME_LENGTH), true)));
        }
        elseif (!$bIsAdmin && !$oMailModel->checkWaitSend($iSenderId, $iTimeDelay, $sCurrentTime))
        {
            \PFBC\Form::setError('form_compose_mail', Form::waitWriteMsg($iTimeDelay));
        }
        elseif (!$bIsAdmin && $oMailModel->isDuplicateContent($iSenderId, $sMessage))
        {
            \PFBC\Form::setError('form_compose_mail', Form::duplicateContentMsg());
        }
        else
        {
            $mSendMsg = $oMailModel->sendMsg($iSenderId, $iRecipientId, $this->httpRequest->post('title'), $sMessage, $sCurrentTime);

            if (false === $mSendMsg)
            {
                \PFBC\Form::setError('form_compose_mail', t('Problem while sending the message. Please try again later.'));
            }
            else
            {
                // If the notification is accepted and the message recipient isn't connected NOW, we send a message.
                if (!$oUserModel->isNotification($iRecipientId, 'newMsg') && $oUserModel->isOnline($iRecipientId, 0))
                {
                    $this->view->content = t('Hello %0%!<br />You have received a new message from <strong>%1%</strong>.<br /> <a href="%2%">Click here</a> to read your message.', $this->httpRequest->post('recipient'), $this->session->get('member_username'), Uri::get('mail', 'main', 'inbox', $mSendMsg));

                    $sRecipientEmail = $oUserModel->getEmail($iRecipientId);

                    $sMessageHtml = $this->view->parseMail(PH7_PATH_SYS . 'global/' . PH7_VIEWS . PH7_TPL_NAME . '/mail/sys/mod/mail/new_msg.tpl', $sRecipientEmail);

                    $aInfo = [
                        'to' => $sRecipientEmail,
                        'subject' => t('New private message from %0% on %site_name%', $this->session->get('member_first_name'))
                    ];

                    (new Mail)->send($aInfo, $sMessageHtml);
                }

                $sUrl = ($bIsAdmin ? Uri::get(PH7_ADMIN_MOD, 'user', 'browse') : Uri::get('mail', 'main', 'index'));
                Header::redirect($sUrl, t('Your message has been sent successfully!'));
            }

            unset($oUserModel, $oMailModel);
        }
    }

}
