<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Note / Controller
 */
namespace PH7;
use PH7\Framework\Mvc\Router\Uri, PH7\Framework\Url\Header;

class AdminController extends MainController
{

    public function index()
    {
        Header::redirect(Uri::get('note', 'main', 'index'), t('Welcome to the Note administrator mode.'));
    }

    public function unmoderated()
    {
        $this->sTitle = t('Unmoderated Notes');
        $this->view->page_title = $this->sTitle;
        $this->view->h1_title = $this->sTitle;

        $this->view->total_pages = $this->oPage->getTotalPages($this->oNoteModel->totalPosts($this->iApproved), 10);
        $this->view->current_page = $this->oPage->getCurrentPage();

        $oPosts = $this->oNoteModel->getPosts($this->oPage->getFirstItem(), $this->oPage->getNbItemsByPage(), SearchCoreModel::CREATED, '0');
        $this->setMenuVars();

        if(empty($oPosts))
        {
            $this->sTitle = t('Not Note Posts for the treatment of moderate.');
            $this->notFound();
        }
        else
        {
            $this->view->posts = $oPosts;
        }

        $this->output();
    }

    public function approved()
    {
        $iNoteId = $this->httpRequest->post('note_id');
        $sPostId = $this->httpRequest->post('post_id');
        $iProfileId = $this->httpRequest->post('profile_id', 'int');

        if(isset($iNoteId, $iProfileId, $sPostId) && $this->oNoteModel->approved($iNoteId))
        {
            /* Clean NoteModel Cache */
            (new Framework\Cache\Cache)->start(NoteModel::CACHE_GROUP, null, null)->clear();

            $this->sMsg = t('The Note has been approved!');
        }
        else
        {
            $this->sMsg = t('Oops! The Note could not be approved!');
        }

        Header::redirect(Uri::get('note','admin','unmoderated'), $this->sMsg);
    }

    public function disapproved()
    {
        $iNoteId = $this->httpRequest->post('note_id');
        $sPostId = $this->httpRequest->post('post_id');
        $iProfileId = $this->httpRequest->post('profile_id', 'int');

        if(isset($iNoteId, $iProfileId, $sPostId) && $this->oNoteModel->approved($iNoteId, '0'))
        {
            /* Clean NoteModel Cache */
            (new Framework\Cache\Cache)->start(NoteModel::CACHE_GROUP, null, null)->clear();

            $this->sMsg = t('The Note has been approved!');
        }
        else
        {
            $this->sMsg = t('Oops! The Note could not be approved!');
        }

        Header::redirect(Uri::get('note', 'main', 'index'), $this->sMsg);
    }

}
