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


function setPublished (date, state) {
    if ((date === undefined) || (state === undefined)) {
        return false;
    }

    var bodyEl      = $('body'),
        sidebarEl   = $('#sidebar'),
        headerEl    = $('#header-review');

    // send request through iframe
    $('#header-review-target').attr(
        'src', window.vars.ENZYME_URL + '/get/publish.php?date=' + date + '&state=' + state
    );

    // remove header
    if (headerEl.length > 0) {
        headerEl.remove();
        bodyEl.removeClass('review');

        if (bodyEl.hasClass('default')) {
            sidebarEl.css('top', parseInt(sidebarEl.css('top'), 10) - 34);
        }
    }
}


$(function () {
    var windowEl = $(window);

    // render map?
    if (window.countryData !== undefined) {
        $('#worldmap').vectorMap({
            map:              'world_mill_en',
            backgroundColor:  '#ffffff',

            series: {
                regions: [
                    {
                        values: window.countryData,
                        scale: [
                            '#B3B3B3', '#8C8C8C', '#666666', '#3F3F3F'
                        ],
                        normalizeFunction: 'polynomial'
                    }
                ]
            },

            onRegionLabelShow: function(e, el, code) {
                if (window.countryData[code] !== undefined) {
                    el.html(el.html() + ' (' + window.countryData[code] + '%)');
                }
            },

            regionStyle: {
                initial: {
                    'fill':             '#ffffff',
                    'stroke':           '#505050',
                    'fill-opacity':     1,
                    'stroke-width':     0.5,
                    'stroke-opacity':   0.5
                },
                hover: {
                    'fill-opacity':     0.8
                }
            }
        });
    }


    // render demographics?
    if ((typeof window.dataset === 'object') &&
        (typeof window.datasetElement === 'object')) {

        // set slice colours
        var colours = [
            '#3B5E7E',
            '#547797',
            '#6D90B0',
            '#86A9C9',
            '#9FC2E2',
            '#B8DBFB'
        ];

        for (var set in window.dataset) {
            // index colours
            for (var key in window.dataset[set]) {
                window.dataset[set][key]['color'] = colours[key];
            }

            // render chart
            $.plot(
                window.datasetElement[set],
                window.dataset[set],
                {
                    series: {
                        pie: {
                            show: true,
                            radius: 1
                        }
                    },
                    legend: {
                        show: true,
                        labelFormatter: function(label, series) {
                            return label + ' (' + series.percent.toFixed(2) + '%)';
                        },
                        labelBoxBorderColor:    '#999999',
                        noColumns:              1,
                        position:               'ne',
                        margin:                 [20, 0]
                    }
                }
            );
        }
    }


    // make contents table scroll to position
    var contents = $('#contents-table');

    if (contents.length > 0) {
        contents
            .off('click.contents')
            .on('click.contents', function (event) {
                event.preventDefault();

                var target = $(event.target);

                if (target.is('a[href^="#"]')) {
                    var hash = target.attr('href'),
                        anchor = $(hash);

                    if (anchor.length > 0) {
                        // accomodate header bar height in scroll?
                        var diff = 0,
                            headerBar = $('#header-bar');

                        if (headerBar.length > 0) {
                            diff = headerBar.outerHeight();
                        }

                        // also show major type header?
                        var prevEl = anchor.prev();
                        if (prevEl.is('h2')) {
                            diff += prevEl.outerHeight() + 24;
                        }

                        // scroll to new position
                        var newPosition = (anchor.offset().top - diff),
                            duration    = Math.max(
                                500,
                                Math.floor(
                                    Math.abs(document.documentElement.scrollTop - newPosition) / 25
                                )
                            );

                        $('html, body').animate({
                            'scrollTop': newPosition
                        }, duration);

                        // update location bar
                        window.location.hash = hash;
                    }
                }

                return false;
            });
    }


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


    // manage position of donate box?
    function shareBox() {
        var sidebar     = $('#sidebar'),
            shareBox    = $('#share-box');

        // do not position if window width is below threshold
        if (windowEl.width() <= 979) {
            windowEl.off('scroll.donate resize.donate');

            shareBox.css({
                'paddingBottom':    'none',
                'bottom':           'auto',
                'left':             'auto',
                'right':            'auto'
            });

            return false;
        }

        if ((sidebar.length > 0) && (shareBox.length > 0)) {
            // move into right column area
            shareBox.css({
                'paddingBottom':    10,
                'left':             'auto',
                'right':            Math.floor(
                    windowEl.width() - (sidebar.offset().left + sidebar.outerWidth())
                )
            });


            // move out of the way of footer when scrolling into bottom area
            var footer = $('footer');

            if (footer.length === 1) {
                var documentHeight = $(document).height(),
                    viewportHeight = windowEl.height(),
                    footerHeight   = footer.outerHeight();

                // get browser info
                var browserInfo = window.browser();

                // check position
                windowEl
                    .off('scroll.donate resize.donate')
                    .on('scroll.donate resize.donate', function (event) {
                        var diff = 20;
                        if (browserInfo[0] === 'Chrome') {
                            diff = 20;
                        }

                        var boundary    = (footerHeight + diff),
                            fromBottom  = (documentHeight - (document.documentElement.scrollTop + viewportHeight));

                        if (fromBottom < boundary) {
                            shareBox.css('bottom', (boundary - fromBottom));

                        } else if (fromBottom < (boundary + 200)) {
                            shareBox.css('bottom', 0);
                        }
                    });
            }
        }
    }

    // check shareBox positioning onload and after resize
    shareBox();

    windowEl
        .off('resize.shareBox')
        .on('resize.shareBox', shareBox);
});