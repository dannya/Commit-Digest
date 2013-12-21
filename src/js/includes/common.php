/*-------------------------------------------------------+
| KDE Commit-Digest
| Copyright 2010-2013 Danny Allen <danny@commit-digest.org>
| http://www.commit-digest.org/
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

// globally-available script variables
var BASE_URL    = '<?php echo BASE_URL; ?>',
    ENZYME_URL  = '<?php echo Config::getSetting("enzyme", "ENZYME_URL"); ?>',
    LANGUAGE    = '<?php echo LANGUAGE; ?>';

// define translatable strings
var strings = {};

strings.failure         = '<?php echo _("Error"); ?>',
strings.close           = '<?php echo _("Close"); ?>',
strings.loading         = '<?php echo _("Loading"); ?>',
strings.cancel          = '<?php echo _("Cancel"); ?>',
strings.account_invalid = '<?php echo _("Error: Account not found"); ?>',
strings.privacy_public  = '<?php echo _("This field is currently <b>public</b>"); ?>',
strings.privacy_private = '<?php echo _("This field is currently <b>private</b>"); ?>';

strings.seconds_ago     = '<?php echo _('%d seconds ago') ?>';
strings.minutes_ago     = '<?php echo _('%d minutes ago') ?>';

// include common JS
<?php
  include_once(BASE_DIR . '/js/includes/common' . MINIFIED . '.js');
?>