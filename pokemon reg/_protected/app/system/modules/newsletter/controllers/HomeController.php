<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Newsletter / Controller
 */
namespace PH7;

class HomeController extends Controller
{

    private $sTitle;

    public function subscription()
    {
        $this->sTitle = t('Subscribe to our newsletter');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = t('Newsletters - Subscribe to our newsletter %site_name% | Social networking, dating website.');
        $this->view->meta_keywords = t('newsletter, newsletters, subscription, email, social, social network, social networking, community, metting, dating, friends, people');
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function activate($sMail, $sHash)
    {
        (new UserCore)->activateAccount($sMail, $sHash, $this->config, $this->registry, 'newsletter');
    }

}
