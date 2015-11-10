<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / User / Form / Processing
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

use PH7\Framework\Mvc\Request\Http;

class EditFormProcess extends Form
{

    public function __construct()
    {
        parent::__construct();

        $oUserModel = new UserModel;
        $iProfileId = (AdminCore::auth() && !User::auth() && $this->httpRequest->getExists('profile_id')) ? $this->httpRequest->get('profile_id', 'int') : $this->session->get('member_id');
        $oUser = $oUserModel->readProfile($iProfileId);

        // For Admins only!
        if ((AdminCore::auth() && !User::auth() && $this->httpRequest->getExists('profile_id')))
        {
            if (!$this->str->equals($this->httpRequest->post('group_id'), $oUser->groupId))
                $oUserModel->updateMembership($this->httpRequest->post('group_id'), $iProfileId);
        }

        if (!$this->str->equals($this->httpRequest->post('first_name'), $oUser->firstName))
        {
            $oUserModel->updateProfile('firstName', $this->httpRequest->post('first_name'), $iProfileId);
            $this->session->set('member_first_name', $this->httpRequest->post('first_name'));

            (new Framework\Cache\Cache)->start(UserCoreModel::CACHE_GROUP, 'firstName' . $iProfileId . 'Members', null)->clear();
        }

        if (!$this->str->equals($this->httpRequest->post('last_name'), $oUser->lastName))
            $oUserModel->updateProfile('lastName', $this->httpRequest->post('last_name'), $iProfileId);

        if (!$this->str->equals($this->httpRequest->post('sex'), $oUser->sex))
        {
            $oUserModel->updateProfile('sex', $this->httpRequest->post('sex'), $iProfileId);
            $this->session->set('member_sex', $this->httpRequest->post('sex'));

            (new Framework\Cache\Cache)->start(UserCoreModel::CACHE_GROUP, 'sex' . $iProfileId . 'Members', null)->clear();
        }

        // WARNING: Be careful, you should use the \PH7\Framework\Mvc\Request\Http::ONLY_XSS_CLEAN constant otherwise the post method of the HttpRequest class removes the tags special
        // and damages the SET function SQL for entry into the database.
        if (!$this->str->equals($this->httpRequest->post('match_sex', Http::ONLY_XSS_CLEAN), $oUser->matchSex))
            $oUserModel->updateProfile('matchSex', Form::setVal($this->httpRequest->post('match_sex', Http::ONLY_XSS_CLEAN)), $iProfileId);

        if (!$this->str->equals($this->dateTime->get($this->httpRequest->post('birth_date'))->date('Y-m-d'), $oUser->birthDate))
            $oUserModel->updateProfile('birthDate', $this->dateTime->get($this->httpRequest->post('birth_date'))->date('Y-m-d'), $iProfileId);

        // Update dynamic fields.
        $oFields = $oUserModel->getInfoFields($iProfileId);
        foreach($oFields as $sColumn => $sValue)
        {
            $sHRParam = ($sColumn == 'description') ? Http::ONLY_XSS_CLEAN : null;
            if (!$this->str->equals($this->httpRequest->post($sColumn, $sHRParam), $sValue))
                $oUserModel->updateProfile($sColumn, $this->httpRequest->post($sColumn, $sHRParam), $iProfileId, 'MembersInfo');
        }
        unset($oFields);

        $oUserModel->setLastEdit($iProfileId);

        /*** Clear caches ***/
        $oUserCache = new User;
        $oUserCache->clearReadProfileCache($iProfileId);
        $oUserCache->clearInfoFieldCache($iProfileId);

        // Destroy objects
        unset($oUserModel, $oUser, $oUserCache);

        \PFBC\Form::setSuccess('form_user_edit_account', t('Your profile has been saved successfully!'));
    }

}
