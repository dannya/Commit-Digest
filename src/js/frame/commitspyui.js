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
  // position spinner
  if ($('spinner')) {
    // position in the middle of the space between the header elements
    $('spinner').style.left = (($('frame').getWidth() - ($('frame-title').getWidth() + $('options').getWidth())) / 2) + $('frame-title').getWidth() + 'px';
  }

  // set up periodical updater
  updateSpy();
  
  // update times every 5 seconds
  window.setInterval(updateTimes, 5000);
});



// set diff for positioning of button
var sharePosDiff = 8;


// set variables
var lastFrequency     = 0;
var periodicalUpdater = null;

// get date for time difference
var date = new Date();


function updateSpy() {
  // ensure needed elements are set 
  if (!$('recent-commits')) {
    return false;
  }

  // set frequency 
  if ($('update-interval')) {
    var theFrequency = ($('update-interval').value * 60);
  } else {
    // default to 5 minutes
    var theFrequency = 300;
  }

  // show spinner
  $('spinner').appear({ duration: 0.3 });

  // initialise updater
  periodicalUpdater = new Ajax.PeriodicalUpdater('recent-commits', BASE_URL + '/get/commit-spy.php', {
    method:     'post',
    frequency:  theFrequency,
    parameters: {
      language:    LANGUAGE,
      timeDiff:    date.getTimezoneOffset(),
      filter:      $('filter').value.strip(),
      filter_type: $('filter-type').value
    },
    onSuccess: function() {
      // hide spinner
      if ($('spinner')) {
        $('spinner').fade({ duration: 0.3 });
      }

      // update times
      window.setTimeout(updateTimes, 5);

      // change frequency?
      if ($('update-interval')) {
        periodicalUpdater.frequency = ($('update-interval').value * 60);
      }
      
      // reset parameters (may have changed)
      periodicalUpdater.options.parameters.filter       = $('filter').value.strip();
      periodicalUpdater.options.parameters.filter_type  = $('filter-type').value;
    }
  });
}  

  
function forceUpdateSpy() {
  if (!$('recent-commits')) {
    return false;
  }

  // show spinner
  if ($('spinner')) {
    $('spinner').appear({ duration: 0.3 });
  }

  // initialise updater
  new Ajax.Request(BASE_URL + '/get/commit-spy.php', {
    method:     'post',
    parameters: {
      timeDiff:    date.getTimezoneOffset(),
      filter:      $('filter').value.strip(),
      filter_type: $('filter-type').value
    },
    onSuccess: function(transport) {
      // hide spinner
      if ($('spinner')) {
        $('spinner').fade({ duration: 0.3 });
      }

      // update text
      $('recent-commits').update(transport.responseText);
      
      // update times
      updateTimes();
    }
  });
}