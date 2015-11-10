<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Blog / Controller
 */
namespace PH7;
use PH7\Framework\Mvc\Router\Uri, PH7\Framework\Url\Header;

class AdminController extends MainController
{

    public function index()
    {
        Header::redirect(Uri::get('blog', 'main', 'index'), t('Welcome to the Blog administrator mode.'));
    }

    public function add()
    {
        $this->sTitle = t('Add a Post');
        $this->view->page_title = $this->sTitle;
        $this->view->h1_title = $this->sTitle;
        $this->output();
    }

    public function edit()
    {
        $this->sTitle = t('Edit the Post');
        $this->view->page_title = $this->sTitle;
        $this->view->h1_title = $this->sTitle;

        $this->output();
    }

    public function delete()
    {
        $iId = $this->httpRequest->post('id');

        CommentCoreModel::deleteRecipient($iId, 'Blog');
        $this->oBlogModel->deleteCategory($iId);
        $this->oBlogModel->deletePost($iId);
        (new Blog)->deleteThumb($iId, 'blog', $this->file);

        /* Clean BlogModel Cache  */
        (new Framework\Cache\Cache)->start(BlogModel::CACHE_GROUP, null, null)->clear();

        Header::redirect(Uri::get('blog', 'main', 'index'), t('Your post was deleted!'));
    }

    private function removeThumb($iId)
    {
        if(!(new Framework\Security\CSRF\Token)->checkUrl())
            exit(Form::errorTokenMsg());

        (new Blog)->deleteThumb($iId, 'blog', $this->file);

        Header::redirect(Uri::get('blog', 'admin', 'edit', $iId), t('The thumbnail has been deleted successfully!'));
    }

}
