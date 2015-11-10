<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Blog / Form / Processing
 */
namespace PH7;
defined('PH7') or die('Restricted access');

use
PH7\Framework\Mvc\Request\Http,
PH7\Framework\Url\Header,
PH7\Framework\Mvc\Router\Uri;

class EditAdminBlogFormProcess extends Form
{

    public function __construct()
    {
        parent::__construct();

        $oBlog = new Blog;
        $oBlogModel = new BlogModel;
        $iBlogId = $this->httpRequest->get('id');
        $sPostId = $oBlogModel->getPostId($iBlogId);
        $oPost = $oBlogModel->readPost($sPostId);

        /*** Updating the ID of the post if it has changed ***/
        $sPostId = $this->httpRequest->post('post_id');
        if(!$this->str->equals($sPostId, $oPost->postId))
        {
            if($oBlog->checkPostId($sPostId))
            {
                $oBlogModel->updatePost('postId', $sPostId, $iBlogId);
                /* Clean BlogModel Cache */
                (new Framework\Cache\Cache)->start(BlogModel::CACHE_GROUP, null, null)->clear();
            }
            else
            {
                \PFBC\Form::setError('form_blog', t('The ID of the article is invalid or incorrect.'));
            }
        }

        // WARNING: Be careful, you should use the \PH7\Framework\Mvc\Request\Http::ONLY_XSS_CLEAN constant otherwise the post method of the HttpRequest class removes the tags special
        // and damages the SET function SQL for entry into the database.
        if(!$this->str->equals($this->httpRequest->post('category_id', Http::ONLY_XSS_CLEAN), $oPost->categoryId))
        {
            $oBlogModel->deleteCategory($iBlogId);

            foreach($this->httpRequest->post('category_id', Http::ONLY_XSS_CLEAN) as $iCategoryId)
                $oBlogModel->addCategory($iCategoryId, $iBlogId);
        }

        // Thumbnail
        $oBlog->setThumb($oPost, $this->file);

        if(!$this->str->equals($this->httpRequest->post('title'), $oPost->title))
            $oBlogModel->updatePost('title', $this->httpRequest->post('title'), $iBlogId);

        // HTML contents, So we use the constant: \PH7\Framework\Mvc\Request\Http::ONLY_XSS_CLEAN
        if(!$this->str->equals($this->httpRequest->post('content', Http::ONLY_XSS_CLEAN), $oPost->content))
            $oBlogModel->updatePost('content', $this->httpRequest->post('content', Http::ONLY_XSS_CLEAN), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('lang_id'), $oPost->langId))
            $oBlogModel->updatePost('langId', $this->httpRequest->post('lang_id'), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('slogan'), $oPost->slogan))
            $oBlogModel->updatePost('slogan', $this->httpRequest->post('slogan'), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('tags'), $oPost->tags))
            $oBlogModel->updatePost('tags', $this->httpRequest->post('tags'), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('page_title'), $oPost->pageTitle))
            $oBlogModel->updatePost('pageTitle', $this->httpRequest->post('page_title'), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('meta_description'), $oPost->metaDescription))
            $oBlogModel->updatePost('metaDescription', $this->httpRequest->post('meta_description'), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('meta_keywords'), $oPost->metaKeywords))
            $oBlogModel->updatePost('metaKeywords', $this->httpRequest->post('meta_keywords'), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('meta_robots'), $oPost->metaRobots))
            $oBlogModel->updatePost('metaRobots', $this->httpRequest->post('meta_robots'), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('meta_author'), $oPost->metaAuthor))
            $oBlogModel->updatePost('metaAuthor', $this->httpRequest->post('meta_author'), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('meta_copyright'), $oPost->metaCopyright))
            $oBlogModel->updatePost('metaCopyright', $this->httpRequest->post('meta_copyright'), $iBlogId);

        if(!$this->str->equals($this->httpRequest->post('enable_comment'), $oPost->enableComment))
            $oBlogModel->updatePost('enableComment', $this->httpRequest->post('enable_comment'), $iBlogId);

        // Updated the modification Date
        $oBlogModel->updatePost('updatedDate', $this->dateTime->get()->dateTime('Y-m-d H:i:s'), $sPostId);
        unset($oBlogModel);

        /* Clean BlogModel Cache */
        (new Framework\Cache\Cache)->start(BlogModel::CACHE_GROUP, null, null)->clear();

        Header::redirect(Uri::get('blog', 'main', 'read', $sPostId),  t('Your post has been saved successfully!'));
    }

}
