<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / User / Controller
 */
namespace PH7;

use PH7\Framework\Navigation\Page;

class VisitorController extends Controller
{

    private $oUserModel, $oVisitorModel, $oPage, $sUsername, $sTitle, $iId, $iTotalVisitors;

    public function __construct()
    {
        parent::__construct();

        /**
         *  If the user is connected, we get his session 'member_username' otherwise we get the username of the url.
         */
        $this->sUsername = (!$this->httpRequest->getExists('username')) ? $this->session->get('member_username') : $this->httpRequest->get('username');

        /**
         * FIRST UPPER FOR THE USERNAME
         * We can do this because the SQL search is case insensitive.
         * Be careful not to do this if you need this user name in the method \PH7\Framework\Layout\Html::getUserAvatar()
         * since it can not find the folder of the user because it is not case insensitive.
         */
        $this->sUsername = $this->str->upperFirst($this->sUsername);

        $this->view->username = $this->sUsername;

        $this->oUserModel = new UserModel;
        $this->iId = $this->oUserModel->getId(null, $this->sUsername);

        $this->oVisitorModel = new VisitorModel($this->iId);
        $this->oPage = new Page;

        $this->view->avatarDesign = new AvatarDesignCore; // Avatar Design Class

        /**
         *  Predefined meta_description.
         */
        $this->view->meta_description = t('The Last Visitors of %0%. Meet new people and make new visitors on your social profile, Make new Visitors and Friends with %site_name%', $this->sUsername);

        /**
         *  Predefined meta_keywords tags.
         */
        $this->view->meta_keywords = t('visitor,friend,dating,social networking,visitors,spy,profile,social,%0%', $this->sUsername);
    }

    public function index()
    {
        $this->view->total_pages = $this->oPage->getTotalPages($this->iTotalVisitors, 10);
        $this->view->current_page = $this->oPage->getCurrentPage();

        $this->iTotalVisitors = $this->oVisitorModel->get($this->httpRequest->get('looking'), true, SearchCoreModel::LAST_VISIT, SearchCoreModel::DESC, null, null);
        $oVisitor = $this->oVisitorModel->get($this->httpRequest->get('looking'), false, SearchCoreModel::LAST_VISIT, SearchCoreModel::DESC, $this->oPage->getFirstItem(), $this->oPage->getNbItemsByPage());

        $this->view->user_views_setting = (UserCore::auth()) ? $this->oUserModel->getPrivacySetting($this->session->get('member_id'))->userSaveViews : '';

        if (empty($oVisitor))
        {
            $this->sTitle = t('No Visitors found for the profile of "%0%"', $this->sUsername);
            $this->view->page_title = $this->sTitle;
            $this->view->h2_title = $this->sTitle;
            $this->view->error = t('Not found visitor.');
        }
        else
        {
            $this->sTitle = t('%0%\'s Visitors:', $this->sUsername);
            $this->view->page_title = $this->sTitle;
            $this->view->h2_title = $this->sTitle;
            $sVisitorTxt = nt('%n% Visitor', '%n% Visitors', $this->iTotalVisitors);
            $this->view->visitor_number = $sVisitorTxt;
            $this->view->visitors = $oVisitor;
        }

        $this->output();
    }

    public function search()
    {
        $this->sTitle = t('Find someone who has visited the profile of %0%', $this->sUsername);
        $this->view->page_title = $this->sTitle;
        $this->view->h2_title = $this->sTitle;
        $this->output();
    }

}
