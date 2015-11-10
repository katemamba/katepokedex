<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Admin / From / Processing
 */
namespace PH7;
defined('PH7') or exit('Restricted access');

use
PH7\Framework\Mvc\Request\Http,
PH7\Framework\Mvc\Router\Uri,
PH7\Framework\Url\Header;

class AdsFormProcess extends Form
{

    public function __construct()
    {
        parent::__construct();

        $iIsAff = (AdsCore::getTable() == 'AdsAffiliates');

        $sTable = AdsCore::getTable();
        $sTitle = $this->httpRequest->post('title');
        $sCode = $this->httpRequest->post('code', Http::NO_CLEAN);
        $aSize = explode('x', $this->httpRequest->post('size'));
        $iWidth = $aSize[0];
        $iHeight = $aSize[1];

        (new AdsCoreModel)->add($sTitle, $sCode, $iWidth, $iHeight, $sTable);

        /* Clean AdminCoreModel Ads and Model\Design for STATIC data */
        (new Framework\Cache\Cache)->start(Framework\Mvc\Model\Design::CACHE_STATIC_GROUP, null, null)->clear()
        ->start(AdsCoreModel::CACHE_GROUP, 'totalAds' . ($iIsAff ? 'Affiliates' : ''), null)->clear();

        $sSlug = ($iIsAff) ? 'affiliate' : '';
        Header::redirect(Uri::get(PH7_ADMIN_MOD, 'setting', 'ads', $sSlug), t('The Advertisement was added successfully!'));
    }

}
