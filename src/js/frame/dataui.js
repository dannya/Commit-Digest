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
        // show code entry box
        if ($('step_2-before') && $('step_2-after') && $('step_2-code')) {
        	$('step_2-before').hide();
        	$('step_2-after').show();
        }
        
        // focus code entry box and observe changes
        if ($('step_2-code')) {
        	$('step_2-code').focus();

        	$('step_2-code').observe('keyup', function(event) {
        		var value = Event.element(event).value.trim();

            // redirect once a likely code has been entered
        		if (value.length == 20) {
        			top.location.href = BASE_URL + '/data/' + value;
        		}
        	});
        }

        // hide later instructions on page
        if ($('step_3')) {
          $('step_3').hide();
        }

	    } else if ((typeof data.invalid != 'undefined') && data.invalid) {
	    	// account name given is not valid
	      alert(strings.account_invalid);

	      // enable inputs
			  $('account-name').enable();
			  $('account-send').enable();
	    }
    }
  });
}


function changePrivacy(event) {
	if ((typeof event != 'object') || !$('access_code')) {
		return false;
	}

  // get involved elements
	var theElement = Event.element(event);
  var theParent  = theElement.up('tr');
  var theField   = theParent.readAttribute('data-field').trim();
  
  if (theElement.type == 'checkbox') {
    var theValue = theElement.checked;
  } else {
  	var theValue = theElement.value;
  }


  // send off change
  new Ajax.Request(BASE_URL + '/get/account-data.php', {
    method:     'post',
    parameters: {
    	context:      'privacy',
    	field:        theField,
    	value:        theValue,
      access_code:  $('access_code').value.trim()
    },
    onSuccess: function(transport) {
      var data = transport.headerJSON; 

      if ((typeof data.success != 'undefined') && data.success) {
    	  var affected = theParent.up('form').select('tr[data-privacy="' + theParent.readAttribute('data-privacy') + '"]');

			  if (((typeof theValue == 'boolean') && (theValue == true)) || 
			      ((typeof theValue == 'string') && (theValue == '1'))) {

			    // private:
			    affected.each(function(row) {
			      // change privacy class
			      row.removeClassName('privacy-public');
			      row.addClassName('privacy-private');
			    });
			
			    // change privacy text
			    if (theField != 'dob') {
            theElement.next('span').update(strings.privacy_private);
			    }
			
			  } else {
			    // public:
			    affected.each(function(row) {
			      // change privacy class
			      row.removeClassName('privacy-private');
			      row.addClassName('privacy-public');
			    });
			
			    // change privacy text
			    if (theField != 'dob') {
			      theElement.next('span').update(strings.privacy_public);
			    }
			  }
      }
    }
  });
}


function save(event) {
	Event.stop(event);
	
	// sanity check
	if (!$('terms_accepted_container') || !$('terms_accepted') || !$('data')) {
		return false;
	}
	
	// check that data terms have been accepted
	if (!$('terms_accepted').checked) {
		// highlight terms checkbox
    new Effect.Highlight($('terms_accepted_container'), {
      startcolor: '#d40000',
      duration:   0.5
    });

    return false;
	}
	
	
	// send off data
	alert('a');

	return false;
}