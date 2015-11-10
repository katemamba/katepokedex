<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Affiliate / Form
 */
namespace PH7;

use PH7\Framework\Parse\SysVar, PH7\Framework\Navigation\Page;

class BannerForm
{

    public static function display()
    {
        $oForm = new \PFBC\Form('form_update_admin_ads', 500);
        $oForm->configure(array('action' => ''));
        $oForm = new \PFBC\Form('form_banner_ads', 500);

        $oPage = new Page;
        $oAdsModel = new AdsCoreModel;
        $oPage->getTotalPages($oAdsModel->total('AdsAffiliates'), 10);
        $oAds = $oAdsModel->get(null, $oPage->getFirstItem(), $oPage->getNbItemsByPage(), 'AdsAffiliates');
        unset($oPage, $oAdsModel);

        $oSysVar = new SysVar;
        foreach ($oAds as $oRow)
        {
            // Begin ads div tags
            $oForm->addElement(new \PFBC\Element\HTMLExternal('<div id="ad_' . $oRow->adsId . '">'));

            $oForm->addElement(new \PFBC\Element\Hidden('id_ads', $oRow->adsId));
            $oForm->addElement(new \PFBC\Element\HTMLExternal('<h2>' . $oRow->name . '</h2>'));
            $oForm->addElement(new \PFBC\Element\HTMLExternal('<p>' . t('Preview Banner:') . '</p>'));
            $oForm->addElement(new \PFBC\Element\HTMLExternal('<div>' . $oSysVar->parse($oRow->code) . '</div>'));
            $oForm->addElement(new \PFBC\Element\Textarea(t('Banner:'),'code', array('readonly'=>'readonly','onclick'=>'this.select()','value'=>$oSysVar->parse($oRow->code))));
            // End ads div tags
            $oForm->addElement(new \PFBC\Element\HTMLExternal('</div>'));
            $oForm->addElement(new \PFBC\Element\HTMLExternal('<br /><hr /><br />'));
        }
        $oForm->render();
    }

}
