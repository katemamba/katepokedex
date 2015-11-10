<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Blog / Form
 */
namespace PH7;

use
PH7\Framework\Str\Str,
PH7\Framework\Security\CSRF\Token,
PH7\Framework\Mvc\Request\Http,
PH7\Framework\Mvc\Router\Uri;

class EditAdminBlogForm
{

    public static function display()
    {
        if (isset($_POST['submit_edit_blog']))
        {
            if (\PFBC\Form::isValid($_POST['submit_edit_blog']))
                new EditAdminBlogFormProcess();

            Framework\Url\Header::redirect();
        }

        $oBlogModel = new BlogModel;

        $iBlogId = (new Http)->get('id', 'int');
        $sPostId = $oBlogModel->getPostId($iBlogId);
        $oPost = $oBlogModel->readPost($sPostId);

        if (!empty($oPost) && (new Str)->equals($iBlogId, $oPost->blogId))
        {
            $oCategoriesData = $oBlogModel->getCategory(null, 0, 300);

            $aCategoriesName = array();
            foreach ($oCategoriesData as $oId)
                $aCategoriesName[$oId->categoryId] = $oId->name;

            $aSelectedCategories = array();
            $oCategoryId = $oBlogModel->getCategory($iBlogId, 0, 300);
            unset($oBlogModel);

            foreach ($oCategoryId as $iId)
                $aSelectedCategories[] = $iId->categoryId;


            $oForm = new \PFBC\Form('form_blog', 650);
            $oForm->configure(array('action' => ''));
            $oForm->addElement(new \PFBC\Element\Hidden('submit_edit_blog', 'form_blog'));
            $oForm->addElement(new \PFBC\Element\Token('edit_blog'));
            $oForm->addElement(new \PFBC\Element\Textbox(t('Title of article:'), 'title', array('value' => $oPost->title, 'validation' => new \PFBC\Validation\Str(2, 100), 'required' => 1)));
            $oForm->addElement(new \PFBC\Element\Textbox(t('Article ID:'), 'post_id', array('value' => $oPost->postId, 'description' => Uri::get('blog', 'main', 'index') . '/<strong><span class="your-address">' . $oPost->postId . '</span><span class="post_id"></span></strong>', 'title' => t('Article ID will be the name of the url.'), 'id' => 'post_id', 'validation' => new \PFBC\Validation\Str(2, 60), 'required' => 1)));
            $oForm->addElement(new \PFBC\Element\HTMLExternal('<div class="label_flow">'));
            $oForm->addElement(new \PFBC\Element\Checkbox(t('Categories:'), 'category_id', $aCategoriesName, array('description' => t('Select a category that best fits your article.'), 'value' => $aSelectedCategories, 'required' => 1)));
            $oForm->addElement(new \PFBC\Element\HTMLExternal('</div>'));
            $oForm->addElement(new \PFBC\Element\CKEditor(t('Contents:'), 'content', array('value' => $oPost->content, 'description' => t('Content of the article'), 'validation' => new \PFBC\Validation\Str(30), 'required' => 1)));
            $oForm->addElement(new \PFBC\Element\Textbox(t('The language of your article:'), 'lang_id', array('value' => $oPost->langId, 'description' => t('EX: "en", "fr", "es", "jp"'), 'validation' => new \PFBC\Validation\Str(2, 2), 'required' => 1)));
            $oForm->addElement(new \PFBC\Element\Textbox(t('Slogan:'), 'slogan', array('value' => $oPost->slogan, 'validation' => new \PFBC\Validation\Str(2, 200))));
            $oForm->addElement(new \PFBC\Element\File(t('Thumbnail:'), 'thumb', array('accept' => 'image/*')));

            $oForm->addElement(new \PFBC\Element\HTMLExternal('<p><br /><img src="' . Blog::getThumb($oPost->blogId) . '" alt="' . t('Thumbnail') . '" title="' . t('The current thumbnail of your post.') . '" class="avatar" /></p>'));

            if (is_file(PH7_PATH_PUBLIC_DATA_SYS_MOD . 'blog/' . PH7_IMG . $iBlogId . '/thumb.png'))
                $oForm->addElement(new \PFBC\Element\HTMLExternal('<a href="' . Uri::get('note', 'main', 'removethumb', $oPost->blogId . (new Token)->url(), false) . '">' . t('Remove this thumbnail?') . '</a>'));

            $oForm->addElement(new \PFBC\Element\Textbox(t('Tags:'), 'tags', array('value' => $oPost->tags, 'description' => t('Separate keywords by commas and without spaces between the commas.'), 'validation' => new \PFBC\Validation\Str(2, 200))));
            $oForm->addElement(new \PFBC\Element\Textbox(t('Title (meta tag):'), 'page_title', array('value' => $oPost->pageTitle, 'validation' => new \PFBC\Validation\Str(2, 200), 'required' => 1)));
            $oForm->addElement(new \PFBC\Element\Textbox(t('Description (meta tag):'), 'meta_description', array('value' => $oPost->metaDescription, 'validation' => new \PFBC\Validation\Str(2, 200))));
            $oForm->addElement(new \PFBC\Element\Textbox(t('Keywords (meta tag):'), 'meta_keywords', array('description' => t('Separate keywords by commas.'), 'value' => $oPost->metaKeywords, 'validation' => new \PFBC\Validation\Str(2, 200))));
            $oForm->addElement(new \PFBC\Element\Textbox(t('Robots (meta tag):'), 'meta_robots', array('value' => $oPost->metaRobots, 'validation' => new \PFBC\Validation\Str(2, 50))));
            $oForm->addElement(new \PFBC\Element\Textbox(t('Author (meta tag):'), 'meta_author', array('value' => $oPost->metaAuthor, 'validation' => new \PFBC\Validation\Str(2, 50))));
            $oForm->addElement(new \PFBC\Element\Textbox(t('Copyright (meta tag):'), 'meta_copyright', array('value' => $oPost->metaCopyright, 'validation' => new \PFBC\Validation\Str(2, 50))));
            $oForm->addElement(new \PFBC\Element\Radio(t('Enable Comment:'), 'enable_comment', array('1' => t('Enable'), '0' => t('Disable')), array('value' => $oPost->enableComment, 'required' => 1)));
            $oForm->addElement(new \PFBC\Element\Button);
            $oForm->addElement(new \PFBC\Element\HTMLExternal('<script src="' . PH7_URL_TPL_SYS_MOD . 'blog/' . PH7_TPL . PH7_TPL_MOD_NAME . PH7_SH . PH7_JS . 'common.js"></script>'));
            $oForm->render();
        }
        else
            echo '<p class="center bold">' . t('Post Not Found!') . '</p>';
    }

}

