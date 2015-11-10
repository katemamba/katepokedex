<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Forum / Controller
 */
namespace PH7;

use
PH7\Framework\Navigation\Page,
PH7\Framework\Security\Ban\Ban,
PH7\Framework\Mvc\Router\Uri,
PH7\Framework\Url\Header;

class ForumController extends Controller
{

    private $oForumModel, $oPage, $sTitle, $sMsg, $iTotalTopics;

    public function __construct()
    {
        parent::__construct();

        $this->oForumModel = new ForumModel;
        $this->oPage = new Page;
        $this->view->avatarDesign = new AvatarDesignCore; // Avatar Design Class
        $this->view->member_id = $this->session->get('member_id');

        // Predefined meta_keywords tags
        $this->view->meta_keywords = t('forum,discussion,dating forum,social forum,people,meet people,forums,free dating forum,free forum,community forum,social forum');

        // Adding Css Style for the Layout Forum
        $this->design->addCss(PH7_LAYOUT . PH7_SYS . PH7_MOD . $this->registry->module . PH7_SH . PH7_TPL . PH7_TPL_MOD_NAME . PH7_SH . PH7_CSS, 'common.css');
    }

    public function index()
    {
        $this->view->total_pages = $this->oPage->getTotalPages($this->oForumModel->totalForums(), 20);
        $this->view->current_page = $this->oPage->getCurrentPage();

        $oCategories = $this->oForumModel->getCategory();
        $oForums = $this->oForumModel->getForum(null, $this->oPage->getFirstItem(), $this->oPage->getNbItemsByPage());

        if (empty($oCategories) && empty($oForums))
        {
            $this->sTitle = t('No Forums found.');
            $this->_notFound();
        }
        else
        {
            $this->view->page_title = t('Discussions Forums - %site_name%');
            $this->view->meta_description = t('Discussions Forums, Social Network Site - %site_name%');
            $this->view->h1_title = t('Discussions Forums, Social Network Site');

            $this->view->categories = $oCategories;
            $this->view->forums = $oForums;
        }

        $this->output();
    }

    public function topic()
    {
        $this->view->total_pages = $this->oPage->getTotalPages($this->oForumModel->totalTopics(), 20);
        $this->view->current_page = $this->oPage->getCurrentPage();
        $oTopics = $this->oForumModel->getTopic(strstr($this->httpRequest->get('forum_name'), '-', true), $this->httpRequest->get('forum_id', 'int'), null, null, null, 1, $this->oPage->getFirstItem(), $this->oPage->getNbItemsByPage());

        $this->view->forum_name = $this->httpRequest->get('forum_name');
        $this->view->forum_id = $this->httpRequest->get('forum_id', 'int');

        if (empty($oTopics))
        {
            $this->sTitle = t('No Topics found.');
            $this->_notFound();
        }
        else
        {
            $this->view->page_title = t('%0% - Forums', $this->str->upperFirst($this->httpRequest->get('forum_name')));
            $this->view->meta_description = t('%0% - Topics - Discussions Forums', $this->httpRequest->get('forum_name'));
            $this->view->meta_keywords = t('%0%,forum,discussion,dating forum,social forum,people,meet people,forums,free dating forum', str_replace(' ', ',', $this->httpRequest->get('forum_name')));
            $this->view->h1_title = $this->str->upperFirst($this->httpRequest->get('forum_name'));
            $this->view->topics = $oTopics;
        }
        $this->output();
    }

    public function post()
    {
        $oPost = $this->oForumModel->getTopic(strstr($this->httpRequest->get('forum_name'), '-', true), $this->httpRequest->get('forum_id', 'int'), strstr($this->httpRequest->get('topic_name'), '-', true), $this->httpRequest->get('topic_id', 'int'), null, 1, 0, 1);

        $this->view->total_pages = $this->oPage->getTotalPages($this->oForumModel->totalMessages($this->httpRequest->get('topic_id', 'int')), 10);
        $this->view->current_page = $this->oPage->getCurrentPage();
        $oMessages = $this->oForumModel->getMessage($this->httpRequest->get('topic_id', 'int'), null, null, 1, $this->oPage->getFirstItem(), $this->oPage->getNbItemsByPage());

        if (empty($oPost))
        {
            $this->sTitle = t('Topic Not Found!');
            $this->_notFound();
        }
        else
        {
            // Adding the RSS link
            $this->view->header = '<link rel="alternate" type="application/rss+xml" title="' . t('Latest Forum Posts') . '" href="' . Uri::get('xml', 'rss', 'xmlrouter', 'forum-post,' . $oPost->topicId) . '" />';
            $this->sTitle = t('%0% | %1% - Forum', $this->str->upperFirst($this->httpRequest->get('forum_name')), $this->str->escape(Ban::filterWord($oPost->title), true));
            $this->view->page_title = $this->sTitle;
            $this->view->meta_description = t('%0% Topics - Discussions Forums', substr($this->str->escape(Ban::filterWord($oPost->message), true), 0, 150));

            // Generates beautiful meta keywords for good SEO
            $this->view->meta_keywords = t('%0%,%1%,forum,discussion,dating forum,social forum', str_replace(' ', ',', $this->httpRequest->get('forum_name')), substr(str_replace(' ', ',', Ban::filterWord($oPost->title, false)), 0, 250));
            $this->view->h1_title = $this->sTitle;

            $this->view->dateTime = $this->dateTime;
            $this->view->post = $oPost;
            $this->view->messages = $oMessages;

            // Set Topics Views Statistics
            Framework\Analytics\Statistic::setView($oPost->topicId, 'ForumsTopics');
        }

        $this->output();
    }

    public function showPostByProfile()
    {
        $sUsername = $this->httpRequest->get('username');
        $this->view->username = $sUsername;

        $iId = (new UserCoreModel)->getId(null, $sUsername);

        $this->iTotalTopics = $this->oForumModel->totalTopics(null, $iId);
        $this->view->total_pages = $this->oPage->getTotalPages($this->iTotalTopics, 20);
        $this->view->current_page = $this->oPage->getCurrentPage();

        $this->view->topic_number = nt('%n% Topic:', '%n% Topics:', $this->iTotalTopics);

        $oTopics = $this->oForumModel->getPostByProfile($iId, 1, $this->oPage->getFirstItem(), $this->oPage->getNbItemsByPage());
        if (empty($oTopics))
        {
            $this->sTitle = t('No found the forum post of %0%.', $sUsername);
            $this->_notFound(false); // Because the Ajax blocks profile, we can not put HTTP error code 404, so the attribute is "false"
        }
        else
        {
            $this->sTitle = t('%0%\'s Forum Posts', $sUsername);
            $this->view->page_title = $this->sTitle;
            $this->view->h2_title = $this->sTitle;
            $this->view->topics = $oTopics;
        }
        $this->output();
    }

    public function search()
    {
        $this->sTitle = t('Search Forum - Looking a Forum Post | %site_name%');
        $this->view->page_title = $this->sTitle;
        $this->view->meta_description = t('Search Topic - Discussion Forum - %site_name%');
        $this->view->h2_title = $this->sTitle;
        $this->output();
    }

    public function result()
    {
        $this->iTotalTopics = $this->oForumModel->search($this->httpRequest->get('looking'), true, $this->httpRequest->get('order'), $this->httpRequest->get('sort'), null, null);
        $this->view->total_pages = $this->oPage->getTotalPages($this->iTotalTopics, 10);
        $this->view->current_page = $this->oPage->getCurrentPage();

        $oSearch = $this->oForumModel->search($this->httpRequest->get('looking'), false, $this->httpRequest->get('order'), $this->httpRequest->get('sort'), $this->oPage->getFirstItem(), $this->oPage->getNbItemsByPage());

        if (empty($oSearch))
        {
            $this->sTitle = t('Sorry, Your search returned no results!');
            $this->_notFound();
        }
        else
        {
            $this->sTitle = t('Forums - Your search returned');
            $this->view->page_title = $this->sTitle;
            $this->view->h3_title = t('%0% Forum Result!', $this->iTotalTopics);
            $this->view->meta_description = t('Search - Discussion Forum');
            $this->view->meta_keywords = t('search,forum,forums,discussion forum');
            $this->view->h2_title = $this->sTitle;
            $this->view->topics = $oSearch;
        }

        $this->manualTplInclude('topic.tpl');
        $this->output();
    }

    public function addTopic()
    {
        $this->sTitle = t('Add a new Topic');
        $this->view->page_title = $this->sTitle;
        $this->view->h2_title = $this->sTitle;
        $this->output();
    }

    public function editTopic()
    {
        $this->sTitle = t('Edit Topic');
        $this->view->page_title = $this->sTitle;
        $this->view->h2_title = $this->sTitle;
        $this->output();
    }

    public function editMessage()
    {
        $this->sTitle = t('Edit your Message');
        $this->view->page_title = $this->sTitle;
        $this->view->h2_title = $this->sTitle;
        $this->output();
    }

    public function reply()
    {
        $this->sTitle = t('Reply Message');
        $this->view->page_title = $this->sTitle;
        $this->view->h2_title = $this->sTitle;
        $this->output();
    }

    public function deleteTopic()
    {
        $aData = explode('_', $this->httpRequest->post('id'));
        $iTopicId = (int) $aData[0];
        $iForumId = (int) $aData[1];
        $sForumName = (string) $aData[2];

        if ($this->oForumModel->deleteTopic($this->session->get('member_id'), $iTopicId))
            $this->sMsg = t('Your topic has been deleted!');
        else
            $this->sMsg = t('Oops! Your topic could not be deleted');

        Header::redirect(Uri::get('forum', 'forum', 'topic', $sForumName . ',' . $iForumId), $this->sMsg);
    }

    public function deleteMessage()
    {
        $aData = explode('_', $this->httpRequest->post('id'));
        $iMessageId = (int) $aData[0];
        $iTopicId = (int) $aData[1];
        $iForumId = (int) $aData[2];
        $sTopicTitle = (string) $aData[3];
        $sForumName = (string) $aData[4];
        unset($aData);

        if ($this->oForumModel->deleteMessage($this->session->get('member_id', 'int'), $iMessageId))
            $this->sMsg = t('Your message has been deleted!');
        else
            $this->sMsg = t('Oops! Your message could not be deleted');

        Header::redirect(Uri::get('forum', 'forum', 'post', $sForumName . ',' . $iForumId . ',' . $sTopicTitle . ',' . $iTopicId), $this->sMsg);
    }

    /**
     * Set a Not Found Error Message with HTTP 404 Code Status.
     *
     * @param boolean $b404Status For the Ajax blocks profile, we can not put HTTP error code 404, so the attribute must be set to "false". Default: TRUE
     * @return void
     */
    private function _notFound($b404Status = true)
    {
        if ($b404Status === true)
            Framework\Http\Http::setHeadersByCode(404);

        $sErrMsg = ($b404Status === true) ? '<br />' . t('Please return to the <a href="%0%">main forum page</a> or <a href="%1%">go the previous page</a>.', Uri::get('forum', 'forum', 'index'), 'javascript:history.back();') : '';

        $this->view->page_title = $this->sTitle;
        $this->view->h2_title = $this->sTitle;
        $this->view->error = $this->sTitle . $sErrMsg;
    }

    public function __destruct()
    {
        unset($this->oForumModel, $this->oPage, $this->sTitle, $this->sMsg, $this->iTotalTopics);
    }

}
