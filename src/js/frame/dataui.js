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
  	// intercept regular form submit
		Event.observe($('account'), 'submit', function(event) {
			Event.stop(event);

      // submit form through function
      accountData();

			return false;
		});


  	// observe keypress so we can enable / disable "send" button
		Event.observe($('account-name'), 'keyup', function(event) {
			Event.stop(event);

      // check input field contents
		  if ($('account-name').value.strip().length > 0) {
		  	// enable "send" button
        $('account-send').enable();

		  } else {
		  	// disable "send" button
		  	$('account-send').disable();
		  }

			return false;
		});
  }
});



// submit account data form
function accountData() {
	// disable inputs
	$('account-name').disable();
	$('account-send').disable();


  // send off account name
  new Ajax.Request(BASE_URL + '/get/account-data.php', {
    method:     'post',
    parameters: {
      account:  $('account-name').value.strip(),
    },
    onSuccess: function(transport) {
      var data = transport.headerJSON; 

      if ((typeof data.success != 'undefined') && data.success) {
        alert('email sent');
      }
    }
  });
}