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


function addRow(container) {
  // make clone, change id's and clear values
  var newRow = container.select('tbody tr').first().clone(true);
  var newNum = container.select('tbody tr').length + 1;

  newRow.select('select, input').each(function(item) {
    // change id
    item.writeAttribute('id', item.readAttribute('id').sub('-1_', '-' + newNum + '_', 1));

    // change name?
    if (item.readAttribute('name') && !item.readAttribute('name').trim().empty()) {
      item.writeAttribute('name', item.readAttribute('name').sub('-1_', '-' + newNum + '_', 1));
    }

    // clear value
    if (item.tagName == 'INPUT') {
    	item.value = '';
      item.focus();

    } else if (item.tagName == 'SELECT') {
      item.selectedIndex = 0;
    }

    // remove any classes
    item.removeClassName('failure');
  });

  // insert cloned row
  Element.insert(container.select('tbody tr').last(), { after: newRow });
}


function submitSurvey(event) {
	if (typeof event == 'object') {
	  Event.stop(event);
	}

	// ensure form is filled
	var error = false;

	$('survey').select('input[type="text"]').each(function(item) {
		// only check row if value has been entered
		if (item.value.trim().empty()) {
			return;
		}

		item.up('tr').select('select').each(function(sub) {
			if (sub.value == '0') {
				// focus first error
				if (!error) {
					sub.focus();
					scrollToOffset(item.up('div.section'), 0, $("lightwindow_contents").down("div.contents"))
				}

        error = true;
				sub.addClassName('failure');

			} else {
        sub.removeClassName('failure');
			}
		});
	});

	var radios = Form.serializeElements($('survey_data').getInputs('radio'), true);

	$('motivation').select('tbody tr').each(function(item) {
		var theName = item.select('label.r1 input[type="radio"]').first().readAttribute('name');	

		if (typeof radios[theName] != 'string') {
      // focus first error
        if (!error) {
        	scrollToOffset(item, 0, $("lightwindow_contents").down("div.contents"))
        }

			error = true;
			item.addClassName('failure');

		} else {
			item.removeClassName('failure');
		}
	});


  if (error) {
  	alert('Please answer all questions');

  } else {
    if ($('submit')) {
      $('submit').disabled = true;
    }

  	// send off data
	  new Ajax.Request(BASE_URL + '/get/survey-data.php', {
	    method:     'post',
	    parameters: $('survey_data').serialize(true),
	    onSuccess: function(transport) {
	      var data = transport.headerJSON; 
	
	      if ((typeof data.success != 'undefined') && data.success) {
	      	// remember survey completion
	      	if ($('survey_done')) {
	      		$('survey_done').writeAttribute('value', 1);
	      	}

	        // close lightbox
	        if (typeof lightbox == 'object') {
	          lightbox.deactivate();
	          $('body').scrollTo();
	        }

	        // thank user
	        alert('Thanks for completing the survey!');

	      } else {
	        // failure
	        if ($('submit')) {
	          $('submit').disabled = false;
	        }

	        alert(strings.failure);
	      }
	    }
	  });
  }

  return false;
}


function radioMouseover(event) {
  var element = event.element();
  if (element.tagName == 'INPUT') {
    element = element.up('label');
  }

  // change tooltip text
  $('tooltip').update(element.readAttribute('title'));

  // get dimensions
  var pos   = element.positionedOffset();
  var size  = $('tooltip').getDimensions();

  // position tooltip
  $('tooltip').setStyle({
    'top':  ((pos.top - size.height) - 4) + 'px',
    'left': ((pos.left - (size.width / 2)) + (element.getDimensions().width / 2)) + 'px'
  });

  // show tooltip
  $('tooltip').show();
}


function radioMouseout(event) {
  // hide tooltip
  $('tooltip').hide();
}


function radioClick(event) {
  var element = event.element().up('label');
  
  // remove error class
  element.up('tr').removeClassName('failure');

  // remove selected class from adjacent labels
  element.up('tr').select('label').invoke('removeClassName', 'selected');

  // add selected class to clicked label
  element.addClassName('selected');
}