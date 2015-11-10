<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Note / Asset / Ajax
 */
namespace PH7;
defined('PH7') or exit('Restricted access');
use PH7\Framework\Mvc\Request\Http;

$oHttpRequest = new Http;

$iStatus = 0; // Error Default Value

if($oHttpRequest->postExists('post_id'))
{
    $iStatus = ((new Note)->checkPostId($oHttpRequest->post('post_id'), $oHttpRequest->post('profile_id'))) ? 1 : 0;
}

echo json_encode(array('status' => $iStatus));

unset($oHttpRequest);
