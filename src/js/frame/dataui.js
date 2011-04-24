/*-------------------------------------------------------+
| KDE Commit-Digest
| Copyright 2010-2011 Danny Allen <danny@commit-digest.org>
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


document.observe('dom:loaded', function() {
  if ($('account-name')) {
  	// observe keypress so we can enable / disable "send" button
		Event.observe($('account-name'), 'keyup', function() {
		  if ($('account-name').value.length > 0) {
		  	// enable "send" button
        $('account-send').enable();

		  } else {
		  	// disable "send" button
		  	$('account-send').disable();
		  }
		});
  }
});



// submit account data form
function accountData() {
	alert($('account-name').value);
}