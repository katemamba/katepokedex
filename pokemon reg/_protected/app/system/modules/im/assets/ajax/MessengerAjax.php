<?php
/**
 * @title          Chat Messenger Ajax
 *
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / IM / Asset / Ajax
 * @version        1.4
 * @required       PHP 5.4 or higher.
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

use
PH7\Framework\Session\Session,
PH7\Framework\File\Import,
PH7\Framework\Parse\Emoticon,
PH7\Framework\Mvc\Router\Uri,
PH7\Framework\Http\Http,
PH7\Framework\Mvc\Request\Http as HttpRequest;

class MessengerAjax
{

    private $_oHttpRequest, $_oMessengerModel;

    public function __construct()
    {
        Import::pH7App(PH7_SYS . PH7_MOD . 'im.models.MessengerModel');

        $this->_oHttpRequest = new HttpRequest;
        $this->_oMessengerModel = new MessengerModel;

        switch ($this->_oHttpRequest->get('act'))
        {
            case 'heartbeat':
                $this->heartbeat();
            break;

            case 'send':
                $this->send();
            break;

            case 'close':
                $this->close();
            break;

            case 'startsession':
                $this->startSession();
            break;

            default:
                Framework\Http\Http::setHeadersByCode(400);
                exit('Bad Request Error!');
        }

        if (empty($_SESSION['messenger_history']))
            $_SESSION['messenger_history'] = [];

        if (empty($_SESSION['messenger_openBoxes']))
            $_SESSION['messenger_openBoxes'] = [];
    }

    protected function heartbeat()
    {
        // Default values
        $sFrom = $_SESSION['messenger_username'];
        $sSent = '';


        $oQuery = $this->_oMessengerModel->select($sFrom);
        $sItems = '';

        foreach ($oQuery as $oData)
        {
            $sFrom = escape($oData->fromUser, true);
            $sSent = escape($oData->sent, true);
            $sMsg = $this->sanitize($oData->message);
            $sMsg = Emoticon::init($sMsg, false);

            if (!isset($_SESSION['messenger_openBoxes'][$sFrom]) && isset($_SESSION['messenger_history'][$sFrom]))
                $sItems = $_SESSION['messenger_history'][$sFrom];

            $sItems .= $this->setJsonContent(['user' => $sFrom, 'msg' => $sMsg]);

            if (!isset($_SESSION['messenger_history'][$sFrom]))
                $_SESSION['messenger_history'][$sFrom] = '';

            $_SESSION['messenger_history'][$sFrom] .= $this->setJsonContent(['user' => $sFrom, 'msg' => $sMsg]);

            unset($_SESSION['messenger_boxes'][$sFrom]);
            $_SESSION['messenger_openBoxes'][$sFrom] = $sSent;
        }

        if (!empty($_SESSION['messenger_openBoxes']))
        {
            foreach ($_SESSION['messenger_openBoxes'] as $sBox => $iTime)
            {
                if (!isset($_SESSION['messenger_boxes'][$sBox]))
                {
                    $iNow = time() - strtotime($iTime);
                    $sTime = date('g:iA M dS', strtotime($iTime));

                    $sMsg = t('Sent at %0%', Framework\Date\Various::textTimeStamp($sTime));
                    if ($iNow > 180)
                    {
                        $sItems .= $this->setJsonContent(['status' => '2', 'user' => $sBox, 'msg' => $sMsg]);

                        if (!isset($_SESSION['messenger_history'][$sBox]))
                            $_SESSION['messenger_history'][$sBox] = '';

                        $_SESSION['messenger_history'][$sBox] .= $this->setJsonContent(['status' => '2', 'user' => $sBox, 'msg' => $sMsg]);
                        $_SESSION['messenger_boxes'][$sBox] = 1;
                    }
                }
            }
        }

        if (!$this->isOnline($sFrom))
            $sItems = t('You must have the ONLINE status in order to speak instantaneous.');
        elseif (!$this->isOnline($sSent))
            $sItems = '<small><em>' . t('%0% is offline. Send a <a href="%1%">Private Message</a> instead.', $sSent, Uri::get('mail','main','compose', $sSent)) . '</em></small>';
        else
            $this->_oMessengerModel->update($sFrom);

        if ($sItems != '')
            $sItems = substr($sItems, 0, -1);

        Http::setContentType('application/json');
        echo '{"items": [' . $sItems . ']}';
        exit(0);
    }

    protected function boxSession($sBox)
    {
        $sItems = '';

        if (isset($_SESSION['messenger_history'][$sBox]))
            $sItems = $_SESSION['messenger_history'][$sBox];

        return $sItems;
    }

    protected function startSession()
    {
        $sItems = '';
        if (!empty($_SESSION['messenger_openBoxes']))
            foreach ($_SESSION['messenger_openBoxes'] as $sBox => $sVoid)
                $sItems .= $this->boxSession($sBox);

        if ($sItems != '')
            $sItems = substr($sItems, 0, -1);

        Http::setContentType('application/json');
        echo '{
            "user": "' . $_SESSION['messenger_username'] . '",
            "items": [' . $sItems . ']
        }';

        exit(0);
    }

    protected function send()
    {
        $sFrom = $_SESSION['messenger_username'];
        $sTo = $this->_oHttpRequest->post('to');
        $sMsg = $this->_oHttpRequest->post('message');

        $_SESSION['messenger_openBoxes'][$this->_oHttpRequest->post('to')] = date('Y-m-d H:i:s', time());

        $sMsgTransform = $this->sanitize($sMsg);
        $sMsgTransform = Emoticon::init($sMsgTransform, false);

        if (!isset($_SESSION['messenger_history'][$this->_oHttpRequest->post('to')]))
            $_SESSION['messenger_history'][$this->_oHttpRequest->post('to')] = '';

        if (!$this->isOnline($sFrom))
            $sMsgTransform = t('You must have the ONLINE status in order to chat with other members.');
        elseif (!$this->isOnline($sTo))
            $sMsgTransform = '<small><em>' . t('%0% is offline. Send a <a href="%1%">Private Message</a> instead.', $sTo, Uri::get('mail','main','compose', $sTo)) . '</em></small>';
        else
            $this->_oMessengerModel->insert($sFrom, $sTo, $sMsg, (new \PH7\Framework\Date\CDateTime)->get()->dateTime('Y-m-d H:i:s'));

        $_SESSION['messenger_history'][$this->_oHttpRequest->post('to')] .= $this->setJsonContent(['status' => '1', 'user' => $sTo, 'msg' => $sMsgTransform]);


        unset($_SESSION['messenger_boxes'][$this->_oHttpRequest->post('to')]);

        Http::setContentType('application/json');
        echo $this->setJsonContent(['user' => $sFrom, 'msg' => $sMsgTransform], false);
        exit(0);
    }

    protected function close()
    {
        unset($_SESSION['messenger_openBoxes'][$this->_oHttpRequest->post('box')]);
        echo '1';
        exit(0);
    }

    protected function setJsonContent(array $aData, $bEndComma = true)
    {
        // Default array
        $aDefData = [
            'status' => '0',
            'user' => '',
            'msg' => ''
        ];

        // Update array
        $aData += $aDefData;

        $sJsonData = <<<EOD
        {
            "status": "{$aData['status']}",
            "user": "{$aData['user']}",
            "msg": "{$aData['msg']}"
        }
EOD;
        return ($bEndComma) ? $sJsonData . ',' : $sJsonData;
    }

    protected function isOnline($sUsername)
    {
        $oUserModel = new UserCoreModel;
        $iProfileId = $oUserModel->getId(null, $sUsername);
        $bIsOnline = $oUserModel->isOnline($iProfileId, Framework\Mvc\Model\DbConfig::getSetting('userTimeout'));
        unset($oUserModel);
        return $bIsOnline;
    }

    protected function sanitize($sText)
    {
        $sText = escape($sText);
        $sText = str_replace("\n\r", "\n", $sText);
        $sText = str_replace("\r\n", "\n", $sText);
        $sText = str_replace("\n", "<br>", $sText);
        return $sText;
    }

    public function __destruct()
    {
        unset($this->_oHttpRequest, $this->_oMessengerModel);
    }

}

// Go only is the member id connected
if (UserCore::auth())
{
    $oSession = new Session; // Go start_session() function.
    if (empty($_SESSION['messenger_username'])) {
        $_SESSION['messenger_username'] = $oSession->get('member_username');
    }
    unset($oSession);
    new MessengerAjax;
}
