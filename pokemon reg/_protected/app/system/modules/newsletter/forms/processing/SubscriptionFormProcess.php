<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Newsletter / Form / Processing
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

use
PH7\Framework\Util\Various,
PH7\Framework\Cookie\Cookie,
PH7\Framework\Ip\Ip,
PH7\Framework\Date\CDateTime,
PH7\Framework\Mvc\Router\Uri,
PH7\Framework\Mail\Mail;

class SubscriptionFormProcess extends Form
{

    public function __construct()
    {
        parent::__construct();

        $oSubscriptionModel = new SubscriptionModel;
        $sEmail = $this->httpRequest->post('email');
        $sName = $this->httpRequest->post('name');
        $bIsSubscriber = (new ExistsCoreModel)->email($sEmail, 'Subscribers');

        switch ($this->httpRequest->post('direction'))
        {
            case 'subscrire':
            {
                if (!$bIsSubscriber)
                {
                    $aData = [
                        'name' => $sName,
                        'email' => $sEmail,
                        'current_date' => (new CDateTime)->get()->dateTime('Y-m-d H:i:s'),
                        'ip' => Ip::get(),
                        'hash_validation' => Various::genRnd(),
                        'active' => '0',
                        'affiliated_id' => (int) (new Cookie)->get(AffiliateCore::COOKIE_NAME)
                    ];

                    $sActivateLink = Uri::get('newsletter','home','activate') . PH7_SH . $aData['email'] . PH7_SH . $aData['hash_validation'];

                    $this->view->content = t('Hi %0%!') . '<br />' .
                    t('Welcome to %site_name% Subscription!', $aData['name']) . '<br />' .
                    t('Activation link: %0%.', '<a href="' . $sActivateLink . '">' . $sActivateLink . '</a>');
                    $this->view->footer = t('You are receiving this mail because we received an application for registration with the email "%0%" has been provided in the form of %site_name% (%site_url%).', $aData['email']) . '<br />' .
                    t('If you think someone has used your email address without your knowledge to create an account on %site_name%, please contact us using our contact form available on our website.');

                    $sMessageHtml = $this->view->parseMail(PH7_PATH_SYS . 'global/' . PH7_VIEWS . PH7_TPL_NAME . '/mail/sys/mod/newsletter/registration.tpl', $sEmail);

                    $aInfo = [
                        'subject' => t('Confirm you email address!'),
                        'to' => $sEmail
                    ];

                    if ( (new Mail)->send($aInfo, $sMessageHtml) )
                    {
                        \PFBC\Form::setSuccess('form_subscription', t('Please activate your subscription by clicking the activation link you received by email. If you can not find the email, please look in your SPAM FOLDER and mark as not spam.'));
                        $oSubscriptionModel->add($aData);
                    }
                    else
                    {
                        \PFBC\Form::setError('form_subscription', Form::errorSendingEmail());
                    }
                }
                else
                {
                    \PFBC\Form::setError('form_subscription', t('Oops! You are already subscribed to our newsletter.'));
                }
            }
            break;

            case 'unsubscribe':
            {
                if ($bIsSubscriber)
                {
                    $oSubscriptionModel->unsubscribe($sEmail);
                    \PFBC\Form::setSuccess('form_subscription', t('Your subscription was successfully canceled.'));
                }
                else
                {
                    \PFBC\Form::setError('form_subscription', t('We have not found any subscriber with the email address.'));
                }
            }
            break;

            default:
                Framework\Http\Http::setHeadersByCode(400);
                exit('Bad Request Error!');
        }
        unset($oSubscriptionModel);
    }

}
