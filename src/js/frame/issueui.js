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


$(function () {
    // setup flattr button
    FlattrLoader.setup();

    FlattrLoader.render(
        {
            'uid':          'dannya',
            'button':       'default',
            'language':     'en_GB',
            'category':     'text',
            'url':          theUrl,
            'title':        theTitle,
            'description':  theDescription
        }, 'flattr', 'replace'
    );


    // manage display of donate box
    var sidebar = $('#sidebar'),
        shareBox = $('#share-box');

    if ((sidebar.length > 0) && (shareBox.length > 0)) {
        // move into right column area
        shareBox.css({
            'paddingBottom':    10,
            'left':             'auto',
            'right':            Math.floor(
                $(window).width() - (sidebar.offset().left + sidebar.outerWidth())
            )
        });


        // move out of the way of footer when scrolling into bottom area
        var observeScroll = function () {
            var footer = $('footer');

            if (footer.length === 1) {
                var documentHeight = $(document).height(),
                    viewportHeight = $(window).height(),
                    footerHeight   = footer.outerHeight();

                // get browser info
                var browserInfo = window.browser();

                $(window)
                    .off('scroll.donate')
                    .on('scroll.donate', function (event) {
                        var diff = -3;
                        if (browserInfo[0] === 'Chrome') {
                            diff = 22;
                        }

                        var boundary = (footerHeight + diff),
                            fromBottom = (documentHeight - (window.scrollY + viewportHeight));

                        if (fromBottom < boundary) {
                            shareBox.css('bottom', (boundary - fromBottom));

                        } else if (fromBottom < (boundary + 200)) {
                            shareBox.css('bottom', 0);
                        }
                    });
            }
        };


        // run onload and reset onresize
        observeScroll();

        $(window)
            .off('resize.donate')
            .on('resize.donate', observeScroll);
    }
});