<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / User / Asset / Ajax
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

class FriendAjax extends Core
{

    private $_oFriendModel, $_sMsg;

    /**
     * @var mixed (boolean or string) $mStatus
     */
    private $_mStatus;

    public function __construct()
    {
        parent::__construct();

        if (!(new Framework\Security\CSRF\Token)->check('friend'))
        exit(jsonMsg(0, Form::errorTokenMsg()));

        $this->_oFriendModel = new FriendModel;

        switch ($this->httpRequest->post('type'))
        {
            case 'add':
                $this->add();
            break;

            case 'approval';
                $this->approval();
            break;

            case 'delete':
                $this->delete();
            break;

            default:
                Framework\Http\Http::setHeadersByCode(400);
                exit('Bad Request Error!');
        }
    }

    protected function add()
    {
        $iFriendId = $this->httpRequest->post('friendId', 'int');
        $iMemberId = $this->session->get('member_id');

        if ($iMemberId == $iFriendId)
        {
            $this->_sMsg = jsonMsg(0, t('You cannot be your own friend.'));
        }
        else
        {
            $this->_mStatus = $this->_oFriendModel->add($this->session->get('member_id'), $iFriendId, $this->dateTime->get()->dateTime('Y-m-d H:i:s'));

            if ($this->_mStatus == 'error')
            {
                $this->_sMsg = jsonMsg(0, t('Unable to add to friends list, please try later.'));
            }
            elseif ($this->_mStatus == 'friend_exists')
            {
                $this->_sMsg = jsonMsg(0, t('This profile already exists in your friends list.'));
            }
            elseif ($this->_mStatus == 'id_does_not_exist')
            {
                $this->_sMsg = jsonMsg(0, t('Profile ID does not exist.')); // Should never happen unless someone changes the source code with firebug or other
            }
            elseif ($this->_mStatus == 'success')
            {
                $this->_sMsg = jsonMsg(1, t('This profile has been successfully added to your friends list.'));

                $oUserModel = new UserCoreModel;
                if (!$oUserModel->isNotification($iFriendId, 'friendRequest') && $oUserModel->isOnline($iFriendId, 0))
                {
                    // Send mail if the notification is accepted and the user isn't connected NOW.
                    $this->sendMail($iFriendId, $oUserModel);
                }
                unset($oUserModel);
            }
        }

        echo $this->_sMsg;
    }

    protected function approval()
    {
        $this->_mStatus = $this->_oFriendModel->approval($this->session->get('member_id'), $this->httpRequest->post('friendId'));

        if (!$this->_mStatus)
        {
            $this->_sMsg = jsonMsg(0, t('Cannot approve friend, please try later.'));
        }
        else
        {
            $this->_sMsg = jsonMsg(1, t('The friends has been approved.'));
        }

        echo $this->_sMsg;
    }

    protected function delete()
    {
        $this->_mStatus = $this->_oFriendModel->delete($this->session->get('member_id'), $this->httpRequest->post('friendId'));

        if (!$this->_mStatus)
        {
            $this->_sMsg = jsonMsg(0, t('Cannot remove friend, please try later.'));
        }
        else
        {
            $this->_sMsg = jsonMsg(1, t('The friends we been deleted.'));
        }

        echo $this->_sMsg;
    }

    /**
     * Send an email to warn the friend request.
     *
     * @param int $iId friend ID
     * @param object \PH7\UserCoreModel $oUserModel
     * @return void
     */
    protected function sendMail($iId, UserCoreModel $oUserModel)
    {
        $sFriendEmail = $oUserModel->getEmail($iId);
        $sFriendUsername = $oUserModel->getUsername($iId);

        /**
         * Note: The predefined variables as %site_name% does not work here,
         * because we are in an ajax script that is called before the definition of these variables.
         */

        /**
         * Get the site name, because we do not have access to predefined variables.
         */
        $sSiteName = Framework\Mvc\Model\DbConfig::getSetting('siteName');

        $this->view->content = t('Hello %0%!<br /><strong>%1%</strong> sent you a friendship request on %2%.<br /> <a href="%3%">Click here</a> to see your friend request.', $sFriendUsername, $this->session->get('member_username'), $sSiteName, Framework\Mvc\Router\Uri::get('user', 'friend', 'index'));

        /* Because we work in Ajax, the constant "PH7_TPL_NAME" is not yet defined.
         * So we use the constant "PH7_DEFAULT_THEME" is already defined.
         */
        $sMessageHtml = $this->view->parseMail(PH7_PATH_SYS . 'global/' . PH7_VIEWS . PH7_DEFAULT_THEME . '/mail/sys/mod/user/friend_request.tpl', $sFriendEmail);

        $aInfo = [
            'to' => $sFriendEmail,
            'subject' => t('%0% wants to be friends with you on %1%', $this->session->get('member_first_name'), $sSiteName)
        ];

        (new Framework\Mail\Mail)->send($aInfo, $sMessageHtml);
    }

    public function __destruct()
    {
        parent::__destruct();

        unset($this->_oFriendModel, $this->_sMsg, $this->_mStatus);
    }

}

// Only Members
if (User::auth())
    new FriendAjax;
