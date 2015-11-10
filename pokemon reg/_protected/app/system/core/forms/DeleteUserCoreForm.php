<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Core / Form
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

/** For "user" and "affiliate" module **/
class DeleteUserCoreForm
{

    public static function display()
    {
        if (isset($_POST['submit_delete_account']))
        {
            if (\PFBC\Form::isValid($_POST['submit_delete_account']))
                new DeleteUserCoreFormProcess();

            Framework\Url\Header::redirect();
        }

        $oForm = new \PFBC\Form('form_delete_account', 500);
        $oForm->configure(array('action' => '' ));
        $oForm->addElement(new \PFBC\Element\Hidden('submit_delete_account', 'form_delete_account'));
        $oForm->addElement(new \PFBC\Element\Token('delete_account'));
        $oForm->addElement(new \PFBC\Element\Password(t('Your Password:'), 'password', array('required' => 1 )));
        $oForm->addElement(new \PFBC\Element\Textarea(t('Your message for your delete account:'), 'message', array('required' =>1, 'validation'=>new \PFBC\Validation\Str(5,500))));
        $oForm->addElement(new \PFBC\Element\Radio(t('Why:'), 'why_delete', array(t('I do not like the site'), t('I met someone'), t('Other, I put the answer in the message!')), array('required' =>1)));
        $oForm->addElement(new \PFBC\Element\CCaptcha(t('Captcha:'), 'captcha', array('description' =>t('Enter the code above:'))));
        $oForm->addElement(new \PFBC\Element\Button);
        $oForm->render();
    }

}
