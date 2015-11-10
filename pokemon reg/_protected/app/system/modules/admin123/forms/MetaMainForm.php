<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Admin / From
 */
namespace PH7;

use
PH7\Framework\Mvc\Model\DbConfig,
PH7\Framework\File\File,
PH7\Framework\Mvc\Request\Http,
PH7\Framework\Mvc\Router\Uri;

class MetaMainForm
{

    public static function display()
    {
        if (isset($_POST['submit_meta']))
        {
            if (\PFBC\Form::isValid($_POST['submit_meta']))
                new MetaMainFormProcess;

            Framework\Url\Header::redirect();
        }

        $sWhereLang = (new Http)->get('meta_lang');
        $oMeta = DbConfig::getMetaMain($sWhereLang);

        $oForm = new \PFBC\Form('form_meta', 500);
        $oForm->configure(array('action' => ''));
        $oForm->addElement(new \PFBC\Element\Hidden('submit_meta', 'form_meta'));
        $oForm->addElement(new \PFBC\Element\Token('admin_meta'));

        // Generate the list of languages
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<div class="center divShow">'));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<h3 class="underline"><a href="#showDiv_listLang" title="' . t('Click here to show/hide the languages') . '">' . t('Change language for the Meta Tags') . '</a></h3>'));
        $oForm->addElement(new \PFBC\Element\HTMLExternal('<ul class="hidden" id="showDiv_listLang">'));
        $aLangs = (new File)->getDirList(PH7_PATH_APP_LANG);
        for ($i=0, $iLength = count($aLangs); $i < $iLength; $i++)
        {
            $sAbbrLang = substr($aLangs[$i],0,2);
            $oForm->addElement(new \PFBC\Element\HTMLExternal('<li>' . ($i+1) . ') ' . '<a class="bold" href="' . Uri::get(PH7_ADMIN_MOD, 'setting', 'metamain', $aLangs[$i], false) . '" title="' . t($sAbbrLang) . '">' . t($sAbbrLang) . ' (' . $aLangs[$i] . ')</a></li>'));
        }
        unset($aLangs);
        $oForm->addElement(new \PFBC\Element\HTMLExternal('</ul></div>'));

        $oForm->addElement(new \PFBC\Element\Textbox(t('Language:'), 'lang_id', array('value' => $oMeta->langId, 'description' => t('EX: "en", "fr", "es", "jp"'), 'validation' => new \PFBC\Validation\Str(5, 5), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Home page title:'), 'page_title', array('value' => $oMeta->pageTitle, 'validation' => new \PFBC\Validation\Str(2, 100), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Slogan:'), 'slogan', array('value' => $oMeta->slogan, 'validation' => new \PFBC\Validation\Str(2, 200), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\CKEditor(t('SEO text:'), 'promo_text', array('description' => t('Promotional text to display on the homepage for visitors.'), 'value' => $oMeta->promoText, 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Description (meta tag):'), 'meta_description', array('value' => $oMeta->metaDescription, 'validation' => new \PFBC\Validation\Str(2, 255), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Keywords (meta tag):'), 'meta_keywords', array('description' => t('Separate keywords by commas.'), 'value' => $oMeta->metaKeywords, 'validation' => new \PFBC\Validation\Str(2, 255), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Robots (meta tag):'), 'meta_robots', array('value' => $oMeta->metaRobots, 'validation' => new \PFBC\Validation\Str(2, 50), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Author (meta tag):'), 'meta_author', array('value' => $oMeta->metaAuthor, 'validation' => new \PFBC\Validation\Str(2, 50), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Copyright (meta tag):'), 'meta_copyright', array('value' => $oMeta->metaCopyright, 'validation' => new \PFBC\Validation\Str(2, 50), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Rating (meta tag):'), 'meta_rating', array('value' => $oMeta->metaRating, 'validation' => new \PFBC\Validation\Str(2, 50), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Distribution (meta tag):'), 'meta_distribution', array('value' => $oMeta->metaDistribution, 'validation' => new \PFBC\Validation\Str(2, 50), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Textbox(t('Category (meta tag):'), 'meta_category', array('value' => $oMeta->metaCategory, 'validation' => new \PFBC\Validation\Str(2, 50), 'required' => 1)));
        $oForm->addElement(new \PFBC\Element\Button);
        $oForm->render();
    }

}
