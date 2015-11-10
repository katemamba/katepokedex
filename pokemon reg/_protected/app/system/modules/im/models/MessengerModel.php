<?php
/**
 * @title          Messenger Model
 *
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7/ App / System / Module / IM / Model
 * @version        1.2
 */
namespace PH7;
use PH7\Framework\Mvc\Model\Engine\Db;

class MessengerModel extends Framework\Mvc\Model\Engine\Model
{

    /**
     * Select Data of content messenger.
     *
     * @param string $sTo Username
     * @return object SQL content
     */
    public function select($sTo)
    {
        $rStmt = Db::getInstance()->prepare('SELECT * FROM' . Db::prefix('Messenger') .
            'WHERE (toUser = :to AND recd = 0) ORDER BY messengerId ASC');
        $rStmt->bindValue(':to', $sTo, \PDO::PARAM_STR);
        $rStmt->execute();
        return $rStmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Update Message.
     *
     * @param string $sTo Username
     * @return boolean Returns TRUE on success or FALSE on failure
     */
    public function update($sTo)
    {
        $rStmt = Db::getInstance()->prepare('UPDATE' . Db::prefix('Messenger') .
            'SET recd = 1 WHERE toUser = :to AND recd = 0');
        $rStmt->bindValue(':to', $sTo, \PDO::PARAM_STR);
        return $rStmt->execute();
    }

    /**
     * Add a new message.
     *
     * @param string $sFrom Username
     * @param string $sTo Username 2
     * @param string $sMessage Message content
     * @param string $sDate In date format: 0000-00-00 00:00:00
     * @return boolean Returns TRUE on success or FALSE on failure
     */
    public function insert($sFrom, $sTo, $sMessage, $sDate)
    {
        $rStmt = Db::getInstance()->prepare('INSERT INTO' . Db::prefix('Messenger') .
            '(fromUser, toUser, message, sent) VALUES (:from, :to, :message, :date)');
        $rStmt->bindValue(':from', $sFrom, \PDO::PARAM_STR);
        $rStmt->bindValue(':to', $sTo, \PDO::PARAM_STR);
        $rStmt->bindValue(':message', $sMessage, \PDO::PARAM_STR);
        $rStmt->bindValue(':date', $sDate, \PDO::PARAM_STR);
        return $rStmt->execute();
    }

}
