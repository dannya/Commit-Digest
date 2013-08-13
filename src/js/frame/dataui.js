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


$(function() {
    if ($('#account-name').length > 0) {
        // intercept regular form submit
        $('#account').on('submit', function(event) {
            event.preventDefault();

            // submit form through function
            accountData();

            return false;
        });

        // observe keypress so we can enable / disable "send" button
        $('#account-name').on('keyup', function(event) {
            event.preventDefault();

            // check input field contents
            if ($('#account-name').val().length > 0) {
                // enable "send" button
                $('#account-send').prop('disabled', false);

            } else {
                // disable "send" button
                $('#account-send').prop('disabled', true);
            }

            return false;
        });

    } else {
        // data management UI
        changeContinent(true);
    }
});


// submit account data form
function accountData() {
    // disable inputs
    $('#account-name').prop('disabled', true);
    $('#account-send').prop('disabled', true);

    // send off account name
    $.ajax({
        type:       'POST',
        dataType:   'json',
        url:        BASE_URL + '/get/account-data.php',
        data:       {
            account:  $('#account-name').val()
        },
        success:    function (data, textStatus, jqXHR) {
            if ((typeof data.success != 'undefined') && data.success) {
                // show code entry box
                if ($('#step_2-before') && $('#step_2-after') && $('#step_2-code')) {
                    $('#step_2-before').hide();
                    $('#step_2-after').show();
                }

                // focus code entry box and observe changes
                if ($('#step_2-code')) {
                    $('#step_2-code').focus();

                    $('#step_2-code')
                        .off('keyup.jump')
                        .on('keyup.jump', function (event) {
                            var value = $(this).val();

                            // redirect once a likely code has been entered
                            if (value.length === 20) {
                                top.location.href = BASE_URL + '/data/' + value;
                            }
                        });
                }

                // hide later instructions on page
                if ($('#step_3')) {
                    $('#step_3').hide();
                }

            } else if ((typeof data.invalid != 'undefined') && data.invalid) {
                // account name given is not valid
                alert(strings.account_invalid);

                // enable inputs
                $('#account-name').prop('disabled', false);
                $('#account-send').prop('disabled', false);
            }

        }
    });
}


function changePrivacy(event) {
    if ((typeof event != 'object') || !$('#access_code')) {
        return false;
    }

    // get involved elements
    var theValue,
        theElement = $(event.target),
        theParent  = theElement.parents('tr'),
        theField   = theParent.attr('data-field');

    if (theElement.is('[type="checkbox"]')) {
        theValue = theElement.is(':checked');
    } else {
        theValue = theElement.val();
    }

    // send off change
    $.ajax({
        type:       'POST',
        dataType:   'json',
        url:        BASE_URL + '/get/account-data.php',
        data:       {
            context:      'privacy',
            field:        theField,
            value:        theValue,
            access_code:  $('#access_code').val()
        },
        success:    function (data, textStatus, jqXHR) {
            if ((typeof data.success != 'undefined') && data.success) {
                var affected = theParent.parents('form').find('tr[data-privacy="' + theParent.attr('data-privacy') + '"]');

                if (((typeof theValue == 'boolean') && (theValue === true)) ||
                    ((typeof theValue == 'string') && (theValue == '1'))) {

                    // private:
                    $.each(affected, function () {
                        // change privacy class
                        $(this)
                            .removeClass('privacy-public')
                            .addClass('privacy-private');
                    });

                    // change privacy text
                    if (theField != 'dob') {
                        theElement.next('span').html(strings.privacy_private);
                    }

                } else {
                    // public:
                    $.each(affected, function () {
                        // change privacy class
                        $(this)
                            .removeClass('privacy-private')
                            .addClass('privacy-public');
                    });

                    // change privacy text
                    if (theField != 'dob') {
                        theElement.next('span').html(strings.privacy_public);
                    }
                }
            }
        }
    });
}


function changeContinent(onload) {
    if (!$('#data-continent') || !$('#data-country')) {
        return false;
    }

    // get selected continent
    var continent = $('#data-continent').val();

    // show all options
    $('#data-country option').show();

    // hide options for other continents
    $('#data-country option[class!="' + continent + '"]').hide();

    // re-show empty option
    $('#data-country option[value="0"]').show();

    // set country select to blank option?
    if (!((typeof onload == 'boolean') && onload)) {
        $('#data-country').get(0).selectedIndex = 0;
    }
}


function save(event) {
    event.preventDefault();

    // sanity check
    if (!$('#terms_accepted_container') || !$('#terms_accepted') || !$('#data')) {
        return false;
    }

    // check that data terms have been accepted
    if (!$('#terms_accepted').is(':checked')) {
        // highlight terms checkbox
        $('#terms_accepted_container').addClass('alert');

        return false;

    } else {
        $('#terms_accepted_container').removeClass('alert');
    }

    // set visual states
    var saveButton = $(event.target);
    saveButton.prop('disabled', true);
    if ($('#spinner')) {
        $('#spinner').show();
    }

    // send off data
    $.ajax({
        type:       'POST',
        dataType:   'json',
        url:        BASE_URL + '/get/account-data.php',
        data:       $('form#data').serializeArray(),
        success:    function (data, textStatus, jqXHR) {
            if ((typeof data.success != 'undefined') && data.success) {
                // hide spinner
                saveButton.prop('disabled', false);
                if ($('#spinner')) {
                    $('#spinner').hide();
                }

            } else {
                // failure
                saveButton.prop('disabled', false);
                if ($('#spinner')) {
                    $('#spinner').hide();
                }

                alert(strings.error);
            }
        }
    });

    return false;
}