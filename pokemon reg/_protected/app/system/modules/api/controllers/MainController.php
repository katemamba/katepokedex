<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Module / Api / Controller
 */
namespace PH7;

use PH7\Framework\Http\Rest\Rest;

class MainController extends Controller
{

    use Framework\Api\Api; // Import the Api Trait

    protected $oRest;

    public function __construct()
    {
        parent::__construct();

        $this->oRest = new Rest;
    }

    /**
     * Test if the API works well.
     */
    public function test()
    {
        if ($this->oRest->getRequestMethod() != 'POST')
            $this->oRest->response('', 406);
        else
            $this->oRest->response($this->set(array('return' => 'It Works!')));
    }

}