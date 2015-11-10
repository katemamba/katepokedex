<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Affiliate / Form
 */
namespace PH7;

use
PH7\Framework\Session\Session,
PH7\Framework\Mvc\Request\Http,
PH7\Framework\Mvc\Router\Uri,
PH7\Framework\Date\CDateTime;

class EditForm
{

    public static function display()
    {
        if (isset($_POST['submit_aff_edit_account']))
        {
            if (\PFBC\Form::isValid($_POST['submit_aff_edit_account']))
                new EditFormProcess();

            Framework\Url\Header::redirect();
        }

        $bAdminLogged = (AdminCore::auth() && !Affiliate::auth()); // Check if the admin is logged.

        $oAffModel = new AffiliateModel;
        $oHR = new Http;
        $iProfileId = ($bAdminLogged && $oHR->getExists('profile_id')) ? $oHR->get('profile_id', 'int') : (new Session)->get('affiliate_id');

        $oAff = $oAffModel->readProfile($iProfileId, 'Affiliates');


        // Birth date with the date format for the date picker
        $sBirthDate = (new CDateTime)->get($oAff->birthDate)->date('m/d/Y');

        $oForm = new \PFBC\Form('form_aff_edit_account', 500);
        $oForm->configure(array('action'=> '' ));
        $oForm->addElement(new \PFBC\Element\Hidden('submit_aff_edit_account', 'form_aff_edit_account'));
        $oForm->addElement(new \PFBC\Element\Token('edit_account'));

        if ($bAdminLogged && $oHR->getExists('profile_id'))
        {
            $oForm->addElement(new \PFBC\Element\HTMLExternal('<p class="center"><a class="m_button" href="' . Uri::get('affiliate', 'admin', 'browse') . '">' . t('Back to Browse Affiliates') . '</a></p>'));
        }
        unset($oHR);

        $oForm->addElement(new \PFBC\Element\HTMLExternal('<h2 class="underline">'.t('Global Information:').'</h2>'));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<p class="error">' . t('Attention all your information must be complete, candid and valid.') . '</p>'));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Your First Name:'), 'first_name', array('id'=>'str_first_name', 'onblur'=>'CValid(this.value,this.id,2,20)', 'value'=>$oAff->firstName, 'required'=>1, 'validation'=>new \PFBC\Validation\Str(2,20))));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<span class="input_error str_first_name"></span>'));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Your Last Name:'), 'last_name', array('id'=>'str_last_name', 'onblur'=>'CValid(this.value,this.id,2,20)', 'value'=>$oAff->lastName, 'required'=>1, 'validation'=>new \PFBC\Validation\Str(2,20))));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<span class="input_error str_last_name"></span>'));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Username:'), 'username', array('description'=>t('For site security, you cannot change your username.'), 'disabled'=>'disabled', 'value'=>$oAff->username)));
        $oForm->addElement(new \PFBC\Element\Email(t('Your Email:'), 'mail', array('description'=>t('For site security and to avoid spam, you cannot change your email address.'), 'disabled'=>'disabled', 'value'=>$oAff->email)));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<span class="input_error phone"></span>'));
        $oForm->addElement(new \PFBC\Element\Radio(t('Your Sex:'), 'sex', array('male'=>t('Male'), 'female'=>t('Female')), array('value'=> $oAff->sex,'required'=>1)));
        $oForm->addElement(new \PFBC\Element\Date(t('Your Date of birth:'), 'birth_date', array('id'=>'birth_date', 'onblur'=>'CValid(this.value, this.id)', 'value'=>$sBirthDate, 'validation'=> new \PFBC\Validation\BirthDate, 'required'=>1)));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<span class="input_error birth_date"></span>'));

        // Generate dynamic fields
        $oFields = $oAffModel->getInfoFields($iProfileId, 'AffiliatesInfo');
        foreach ($oFields as $sColumn => $sValue)
            $oForm = (new DynamicFieldCoreForm($oForm, $sColumn, $sValue))->generate();

        $oForm->addElement(new \PFBC\Element\Button);
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<script src="'.PH7_URL_STATIC.PH7_JS.'validate.js"></script>'));
        $oForm->render();
    }

}
