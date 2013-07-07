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


// globally-available script variables
var BASE_URL    = '<?php echo BASE_URL; ?>';
var ENZYME_URL  = '<?php echo Config::getSetting("enzyme", "ENZYME_URL"); ?>';
var LANGUAGE    = '<?php echo LANGUAGE; ?>';



// define translatable strings
var strings  = {};

strings.failure                 = '<?php echo _("Error"); ?>';
strings.close                   = '<?php echo _("Close"); ?>';
strings.loading                 = '<?php echo _("Loading"); ?>';
strings.cancel                  = '<?php echo _("Cancel"); ?>';

strings.account_invalid         = '<?php echo _("Error: Account not found"); ?>';

strings.privacy_public          = '<?php echo _("This field is currently <b>public</b>"); ?>';
strings.privacy_private         = '<?php echo _("This field is currently <b>private</b>"); ?>';



// move sidebar with page scroll
$(function () {
  if ($('#sidebar') && $('#sidebar-logo')) {
    $(window).on('scroll', function () {
      checkPositioning();
    });

    // also run onload (in case browser scroll was already past origin)
    checkPositioning();
  }
});



function checkPositioning() {
  // position sidebar
  if ($('#header-review')) {
    // accomodate review banner
    if ($(window).scrollTop() < 66) {
      var yPos = 100 - $(window).scrollTop();
    } else {
      var yPos = 34;
    }

  } else {
    if ($(window).scrollTop() < 66) {
      var yPos = 66 - $(window).scrollTop();
    } else {
      var yPos = 0;
    }
  }

  $('#sidebar').css({
    'top': yPos
  });

  // show sidebar logo?
  if ($(window).scrollTop() < 72) {
    $('#sidebar-logo').fadeOut(200);
  } else {
    $('#sidebar-logo').fadeIn(200);
  }

  // move share box?
  if ($('#share-box') && $('#footer') && $('#frame')) {
    var scrollPos = ($(window).scrollTop() + $(window).height()),
        pageLimit = (($('#frame').height() + 224) - $('#footer').height()),
        theCss    = {};

    if (scrollPos > pageLimit) {
      if (typeof sharePosDiff != 'undefined') {
        theCss['bottom'] = (scrollPos - pageLimit) + sharePosDiff;
      } else if ($('donate-box')) {
        theCss['bottom'] = (scrollPos - pageLimit) + 20;
      } else {
        theCss['bottom'] = (scrollPos - pageLimit);
      }

    } else {
      theCss['bottom'] = 0;
    }

    // set style
    $('#share-box').css(theCss);
  }
}


function changeLanguage(event) {
  if (typeof event == 'undefined') {
    return false;
  }

  var element = $(event.target);
  location.href = location.protocol + '//' + location.host + location.pathname + '?language=' + element.val();
}


function sprintf() {
  if (!arguments || (arguments.length < 1) || !RegExp) {
    return;
  }

  var str = arguments[0];
  var re  = /([^%]*)%('.|0|\x20)?(-)?(\d+)?(\.\d+)?(%|b|c|d|u|f|o|s|x|X)(.*)/;
  var a = b = [], numSubstitutions = 0, numMatches = 0;

  while (a = re.exec(str)) {
    var leftpart    = a[1], pPad = a[2], pJustify = a[3], pMinLength = a[4];
    var pPrecision  = a[5], pType = a[6], rightPart = a[7];

    ++numMatches;

    if (pType == '%') {
      subst = '%';
    } else {
      ++numSubstitutions;

      if (numSubstitutions >= arguments.length) {
        // not enough args
        return str;
      }

      var param = arguments[numSubstitutions];
      var pad   = '';

      if (pPad && pPad.substr(0,1) == "'") {
        pad = leftpart.substr(1,1);
      } else if (pPad) {
        pad = pPad;
      }

      var justifyRight = true;
      if (pJustify && pJustify === "-") {
        justifyRight = false;
      }

      var minLength = -1;
      if (pMinLength) {
        minLength = parseInt(pMinLength);
      }

      var precision = -1;
      if (pPrecision && pType == 'f') {
        precision = parseInt(pPrecision.substring(1));
      }

      var subst = param;
      if (pType == 'b') {
        subst = parseInt(param).toString(2);
      } else if (pType == 'c') {
        subst = String.fromCharCode(parseInt(param));
      } else if (pType == 'd') {
        subst = parseInt(param) ? parseInt(param) : 0;
      } else if (pType == 'u') {
        subst = Math.abs(param);
      } else if (pType == 'f') {
        subst = (precision > -1) ? Math.round(parseFloat(param) * Math.pow(10, precision)) / Math.pow(10, precision): parseFloat(param);
      } else if (pType == 'o') {
        subst = parseInt(param).toString(8);
      } else if (pType == 's') {
        subst = param;
      } else if (pType == 'x') {
        subst = ('' + parseInt(param).toString(16)).toLowerCase();
      } else if (pType == 'X') {
        subst = ('' + parseInt(param).toString(16)).toUpperCase();
      }
    }

    str = leftpart + subst + rightPart;
  }

  return str;
}


function updateTimes() {
  var currentTime  = new Date();
  currentTime      = currentTime.getTime() + (currentTime.getTimezoneOffset() * 60000);

  $$('div.commit span.timestamp').each(function(timestamp) {
    // determine difference from current date
    var diff = parseInt(timestamp.readAttribute('rel'));
    diff     = Math.round(currentTime / 1000) - diff;

    // update time diff
    if (diff < 60) {
      timestamp.update(sprintf('<?php echo _('%d seconds ago') ?>', diff));
    } else {
      timestamp.update(sprintf('<?php echo _('%d minutes ago') ?>', Math.round(diff / 60)));
    }
  });
}


function inputPrompt(event) {
  var element = event.element();
  var tagname = element.tagName;

  if (event.type == 'focus') {
    // save initial value?
    if (element.value == "<?php echo _('filter?'); ?>") {
      if (!element.readAttribute('alt')) {
        if (tagname == 'TEXTAREA') {
          var text = element.innerHTML;
        } else {
          var text = element.value;
        }

        element.writeAttribute('alt', text);
      }

      element.value = '';
      element.removeClassName('prompt');
    }

  } else if (event.type == 'blur') {
    // switch back?
    if (element.value.empty() == true) {
      element.value = element.readAttribute('alt');
      element.addClassName('prompt');
    }
  }
}


function openInLightbox(event, options) {
  if (typeof event == 'object') {
    Event.stop(event);
    var theLink = event.element().readAttribute('href');

  } else if (typeof event == 'string') {
    var theLink = event;

  } else {
    return false;
  }


  // append to link?
  if (typeof options['append'] == 'string') {
    theLink += options['append'];
  }

  // set and extend options
  var theOptions = Object.extend({
    href:       theLink,
    type:       'page',
    title:      '',
    width:      1000,
    height:     500,
    clickClose: true
  }, options || {});


  // load in lightbox
  lightbox.activateWindow(theOptions);

  return false;
}


function scrollToOffset(id, offset, container) {
  if (!$(id) || (typeof offset == 'undefined')) {
    return false;
  }

  // make sure current item is visible in browser viewport!
  if ((typeof container == 'object') && $(container)) {
    var pos = $(id).positionedOffset();
  } else {
    var pos = $(id).cumulativeOffset();
  }

  var newPos = pos[1] + offset;
  if (newPos < 0) {
    newPos = 0;
  }

  // perform scroll
  if ((typeof container == 'object') && $(container)) {
    $(container).scrollTop  = newPos;
    $(container).scrollLeft = pos[0];

  } else {
    window.scrollTo(pos[0], newPos);
  }
}