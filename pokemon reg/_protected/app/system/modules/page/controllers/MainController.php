<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Page / Controller
 */
namespace PH7;
use PH7\Framework\Mvc\Model\DbConfig;

class MainController extends Controller
{

    private $sTitle;

    public function __construct()
    {
          parent::__construct();
          // Enable caching for all pages of this module
          $this->view->setCaching(true);

          // Global variables for all template pages of the module
          $this->view->admin_email = DbConfig::getSetting('adminEmail');
    }

    public function index()
    {
        $this->view->page_title = t('Online Dating Sie for Single!');
        $this->view->meta_description = t('Meet new people and have meetings near you with %site_name%, the new online dating free site  new generation with webcam chat!');
        $this->view->meta_keywords = t('dating, free dating, online dating, people, meeting, romance, woman, man, dating site, flirt, chat, chat room, webcam, video chat, %site_name%');
        $this->view->h1_title = t('Free Online Dating with %site_name%!');
        $this->view->h2_title = t('Welcome to we Web Dating Site!');
        $this->output();
    }

    public function about()
    {
        $this->sTitle = t('About this web site %site_name%');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = $this->sTitle;
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function help()
    {
        $this->sTitle = t('Help');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = t('Help about of %site_name%');
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function faq()
    {
        $this->design->addCss(PH7_LAYOUT . PH7_SYS . PH7_MOD . $this->registry->module . PH7_SH . PH7_TPL . PH7_TPL_MOD_NAME . PH7_SH . PH7_CSS, 'faq.css');
        // divShow.js for the display/hide questions
        $this->design->addJs(PH7_STATIC . PH7_JS, 'divShow.js');

        $this->view->page_title = t('FAQ');
        $this->sTitle = t('Frequently asked questions of %site_name%');
        $this->view->meta_description = $this->sTitle;
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function terms()
    {
        $this->sTitle = t('Terms and Conditions of Use');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = t('Terms and Conditions of Use, Terms of Use - %site_name%');
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function affiliateTerms()
    {
        $this->sTitle = t('Affiliate Terms and Conditions of Use');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = t('Affiliate Terms and Conditions of Use, Terms of Use - %site_name%');
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function privacy()
    {
        $this->sTitle = t('Privacy Policy');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = t('Privacy Policy - %site_name%');
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function imprint()
    {
        $this->sTitle = t('Imprint');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = t('Imprint - %site_name%');
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function helpUs()
    {
        $this->sTitle = t('Help Us');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = t('Help US - %site_name%');
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function shareSite()
    {
        $this->sTitle = t('Share this site with others!');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = t('Do you like %site_name% and want it even more popular, then share this site on your website, blog, discussion forum, etc...');
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function partner()
    {
        $this->sTitle = t('Partners of %site_name%');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = $this->sTitle;
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function job()
    {
        $this->sTitle = t('Jobs - Careers @ %site_name%');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = $this->sTitle;
        $this->view->h1_title = t('Careers, Jobs and Internships at %site_name%');
        $this->output();
    }

    public function team()
    {
        $this->sTitle = t('%site_name%\'s Team');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = $this->sTitle;
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function link()
    {
        $this->sTitle = t('Links and Partners of %site_name%');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = $this->sTitle;
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

}
