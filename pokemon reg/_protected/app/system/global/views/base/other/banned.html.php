<?php
/**
 * @author         Pierre-Henry Soria <ph7software@gmail.com>
 * @copyright      (c) 2012-2015, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; See PH7.LICENSE.txt and PH7.COPYRIGHT.txt in the root directory.
 * @package        PH7 / App / System / Global / View / Base / Other
 */
namespace PH7;
defined('PH7') or exit('Restricted access');
use PH7\Framework\Layout\Html\Design;

$oDesign = new Design;
$oDesign->htmlHeader();

$aMeta = [
    'title' => t('Free Dating Social Community - Your IP is banned') . ' - ' . Core::SOFTWARE_NAME . ' | ' . Core::SOFTWARE_COMPANY,
    'description' => t('Free Dating Social Community - Your IP is banned') . ' ' . Core::SOFTWARE_DESCRIPTION,
    'keywords' => t('banned,ban,dating site,free online dating social,social network,dating')
];
?>
<!-- Begin Header -->
<?php $oDesign->usefulHtmlHeader($aMeta, true); ?>
<!-- End Header -->

<!-- Begin Content -->
<div id="content" class="s_padd">
<br />
<h1 class="err_msg"><?php echo t('Sorry, your IP or your location is banned!') ?></h1>
<p>
<?php echo t('We are sorry, your IP address or your location is banned.') ?>
<br /><br />
<span class="small italic"><?php echo t('Kind regards, The Team.') ?></span></p>
</div>
<!-- End Content -->

<!-- Begin Footer -->
<footer>
<?php $oDesign->link(); ?>
</footer>
<!-- End Footer -->
<?php $oDesign->htmlFooter(); ?>
