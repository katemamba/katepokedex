<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Invite / Form
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

use PH7\Framework\Mvc\Router\Uri;

class InviteForm
{

    public static function display()
    {
        if (isset($_POST['submit_invite']))
        {
            if (\PFBC\Form::isValid($_POST['submit_invite']))
                new InviteFormProcess();

            Framework\Url\Header::redirect();
        }

        $oForm = new \PFBC\Form('form_invite', 350);
        $oForm->configure(array('action' => Uri::get('invite', 'home', 'invitation')));
        $oForm->addElement(new \PFBC\Element\Hidden('submit_invite', 'form_invite'));
        $oForm->addElement(new \PFBC\Element\Token('invite'));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Your name:'), 'first_name', array('id'=>'str_first_name','onblur'=>'CValid(this.value,this.id,2,20)', 'required'=>1,'validation'=>new \PFBC\Validation\Str('2','20'))));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<span class="input_error str_first_name"></span>'));
        $oForm->addElement(new \PFBC\Element\Textarea(t('To:'), 'to', array('description'=>t('Upto 10 email addresses separated by commas.'), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textarea(t('Message:'), 'message', array('id'=>'str_msg', 'onblur'=>'CValid(this.value,this.id,4)','required'=>1,'validation'=>new \PFBC\Validation\Str('4'))));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<span class="input_error str_msg"></span>'));
        $oForm->addElement(new \PFBC\Element\CCaptcha(t('Captcha:'), 'captcha', array('id' => 'ccaptcha', 'onkeyup'=>'CValid(this.value, this.id)', 'description' => t('Enter the code above:'))));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<span class="input_error ccaptcha"></span>'));
        $oForm->addElement(new \PFBC\Element\Button(t('Invite your friends!'),'submit'));
        $oForm->addElement(new \PFBC\Element\Button(t('Cancel'), 'cancel', array('onclick'=>'parent.$.colorbox.close()')));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<script src="'.PH7_URL_STATIC.PH7_JS.'validate.js"></script><script src="'.PH7_URL_STATIC.PH7_JS.'str.js"></script>'));
        $oForm->render();
    }

}
