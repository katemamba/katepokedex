<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Core / Class
 */
namespace PH7;

class CommentCore
{

    /**
     * @desc Block constructing.
     * @access private
     */
    private function __construct() {}

    /**
     * Check table.
     *
     * @param string $sTable
     * @return mixed (string or void) Returns the table if it is correct.
     * @see \PH7\Framework\Mvc\Model\Engine\Util\Various::launchErr()
     * @throws \PH7\Framework\Mvc\Model\Engine\Util\Various::launchErr() If the table is not valid.
     */
    public static function checkTable($sTable)
    {
        $sTable = strtolower($sTable); // Case insensitivity

        switch ($sTable)
        {
            case 'profile':
            case 'picture':
            case 'video':
            case 'blog':
            case 'note':
            case 'game':
                return ucfirst($sTable);
            break;

            default:
                Framework\Mvc\Model\Engine\Util\Various::launchErr($sTable);
        }
    }

    /**
     * @desc Count Comment with a HTML text.
     * @param integer $iId
     * @param string $sTable
     * @return string
     */
    public static function count($iId, $sTable)
    {
        $iCommentNumber = (new CommentCoreModel)->total($iId, $sTable);
        return nt('%n% Comment', '%n% Comments', $iCommentNumber);
    }

}
