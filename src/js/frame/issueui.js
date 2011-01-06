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
  // position chart legends vertically
  $$("table.plotr-legend").each(function(legend) {
    legend.setStyle({ top: (((legend.up("div[id]").getHeight() - legend.getHeight()) / 2) - 10) + "px" });
  });
});


// specify mappy button possibilities
var nav_buttons = { };
nav_buttons['north-america'] = { 'left':   null,
                                 'right':  'europe',
                                 'top':    null,
                                 'bottom': 'south-america' };
nav_buttons['south-america'] = { 'left':   null,
                                 'right':  'africa',
                                 'top':    'north-america',
                                 'bottom': null };
nav_buttons['europe'] =        { 'left':   'north-america',
                                 'right':  'asia',
                                 'top':    null,
                                 'bottom': 'africa' };
nav_buttons['africa'] =        { 'left':   'south-america',
                                 'right':  'oceania',
                                 'top':    'europe',
                                 'bottom': null };
nav_buttons['asia'] =          { 'left':   'europe',
                                 'right':  null,
                                 'top':    null,
                                 'bottom': 'oceania' };
nav_buttons['oceania'] =       { 'left':   'africa',
                                 'right':  null,
                                 'top':    'asia',
                                 'bottom': null };


var currentContinent = null;

function mappy(event, date, context) {
  if ((typeof date == 'undefined') || (typeof context == 'undefined') || !$('mappy-content-img')) {
    return false;
  }

  // don't follow A link href after this function!
  Event.stop(event);

  if (context == 'list') {
    // show list
    $('mappy-content-prompt').hide();
    $('mappy-content-img').hide();
    $('mappy-content-table').show();

    // change link
    $('mappy-change-list').hide();
    $('mappy-change-map').show();

    setNavButtons(date);

  } else if (context == 'map') {
    // show map
    $('mappy-content-table').hide();
    $('mappy-content-img').show();

    if (!currentContinent) {
      $('mappy-content-prompt').show();
    }

    // change link
    $('mappy-change-list').show();
    $('mappy-change-map').hide();

    setNavButtons(date, currentContinent);
  }

  return false;
}


function getMap(theDate, theData) {
  if ((typeof theDate == 'undefined') ||
      (typeof theData == 'undefined')) {

    return false;
  }
  
  
  // hide list / map button, interferes graphically with loading
  if ($('mappy-change-list')) {
    $('mappy-change-list').hide();
  }
  

  // use proxy to download maps to local
  new Ajax.Request(BASE_URL + '/get/maps.php', {
    method:     'post',
    parameters: {
      date: theDate,
      data: Object.toQueryString(theData)
    },
    onSuccess: function(transport) {
      var data = transport.headerJSON; 

      if ((typeof data.success != 'undefined') && data.success) {
        changeMap(theDate);

        // reshow list / map button
        if ($('mappy-change-list')) {
          $('mappy-change-list').appear({ duration:0.3 });
        }
      }
    }
  });
}


function changeMap(date, continent) {
  if ((typeof date != 'undefined') && $('mappy-content-img')) {
    // show spinner
    if ($('mappy-content-spinner')) {
      $('mappy-content-spinner').show();
    }
    
    // set location of images
    if (false && (date == '2010-10-10')) {
      var imgDir = '/files/stats2';
    } else {
      var imgDir = '/files/stats';
    }

    if (typeof continent == 'undefined') {
      // reload world map
      $('mappy-content-prompt').appear({ duration:0.2 });
      $('mappy-content-img').src = BASE_URL + '/issues/' + date + imgDir + '/standard-embedded_world.png';

      // unset map clicks zoom out
      $('mappy-content-img').writeAttribute('onclick', '');

      // show overlay
      $('mappy-content-overlay').show();

      // unset nav buttons
      currentContinent = null;
      setNavButtons(date);

    } else {
      // change continent
      $('mappy-content-prompt').fade({ duration:0.2 });
      $('mappy-content-img').src = BASE_URL + '/issues/' + date + imgDir + '/standard-embedded_' + continent + '.png';

      // hide overlay
      $('mappy-content-overlay').hide();
      
      // make map clicks zoom out
      $('mappy-content-img').writeAttribute('onclick', 'changeMap(\'' + date + '\');');

      // set nav buttons
      currentContinent = continent;
      setNavButtons(date, continent);
    }
  }
}


function changeMapLoaded() {
  // hide spinner
  if ($('mappy-content-spinner')) {
    $('mappy-content-spinner').hide();
  }
}


function setNavButtons(date, continent) {
  if ((typeof date == 'undefined') || (typeof continent == 'undefined') || 
      !continent || (typeof nav_buttons[continent] == 'undefined')) {

    // unset all buttons
    for (var key in nav_buttons['europe']) {
      if ($('mappy-nav-' + key)) {
        // make not clickable
        $('mappy-nav-' + key).removeClassName('mappy-hover');
        $('mappy-nav-' + key).writeAttribute('onclick', '');
      }
    }

  } else {
    for (var key in nav_buttons[continent]) {
      if ($('mappy-nav-' + key)) {
        if (nav_buttons[continent][key]) {
          // make clickable
          $('mappy-nav-' + key).addClassName('mappy-hover');
          $('mappy-nav-' + key).writeAttribute('onclick', 'changeMap(\'' + date + '\', \'' + nav_buttons[continent][key] + '\');');

        } else {
          // make not clickable
          $('mappy-nav-' + key).removeClassName('mappy-hover');
          $('mappy-nav-' + key).writeAttribute('onclick', '');
        }
      }
    }
  }
}