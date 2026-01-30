// This file is part of Moodle - http://moodle.org/ //
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * General features.
 *
 * @module    block_kamaleon/main
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import horizontalSlider from 'block_kamaleon/horizontalslider';
import Log from 'core/log';
import Modal from 'core/modal';
import ModalEvents from 'core/modal_events';

/**
 * Component initialization.
 */
export const initSlider = () => {

    $(".kam-hslider").each(function() {
        var $this = $(this);

        var prop = {
            "animation": "slide",
            "itemMargin": 15,
            "selector": "section > *",
        };

        if ($this.attr('data-autoplay') && $this.attr('data-autoplay') == 'true') {
            prop.slideshow = true;
        } else {
            prop.slideshow = false;
        }

        if ($this.attr('data-loop') && $this.attr('data-loop') == 'true') {
            prop.animationLoop = true;
        }

        if ($this.attr('data-width')) {
            prop.itemWidth = parseInt($this.attr('data-width'));
        } else {
            var $element = $this.find(prop.selector);
            var itemwidth = $element.css('width');

            if (itemwidth) {
                prop.itemWidth = parseInt(itemwidth);
            } else {
                prop.itemWidth = $element.width();
            }
        }

        horizontalSlider.init($this, prop);
    });
};

/**
 * Initialize open in modal feature.
 */
export const initOpenInModal = () => {
    $(".kam-openinmodal").each(function() {
        var $this = $(this);
        $this.on('click', function(event) {
            event.preventDefault();

            const $link = $(this);

            const dialogue = $link.data('dialogue');

            if (!dialogue) {

                var w = $this.attr('data-property-width');
                var h = $this.attr('data-property-height');

                var href = $link.attr('href');
                var url = href + (href.indexOf('?') === -1 ? '?' : '&') + 'inpopup=true';
                var $iframe = $('<iframe class="bbco-openinmodal-container"></iframe>');
                $iframe.attr('src', url);
                $iframe.on('load', function() {
                    $iframe.contents().find('a:not([target])').attr('target', '_top');
                });

                var el = $.fn.hide;
                $.fn.hide = function() {
                    this.trigger('hide');
                    return el.apply(this, arguments);
                };

                var $floatwindow = $('<div></div>');

                $floatwindow.append($iframe);

                var properties = {
                    width: '95vw',
                    height: '95vh',
                };

                if (w) {
                    if (w.indexOf('%') >= 0) {
                        var windowW = $(window).width();
                        var tmpW = Number(w.replace('%', ''));
                        if (!isNaN(tmpW) && tmpW > 0) {
                            w = tmpW * windowW / 100;
                        }
                    }

                    if (!isNaN(w)) {
                        w += 'px';
                    }

                    properties.width = w;
                }

                if (h) {
                    if (h.indexOf('%') >= 0) {
                        var windowH = $(window).height();
                        var tmpH = Number(h.replace('%', ''));
                        if (!isNaN(tmpH) && tmpH > 0) {
                            h = tmpH * windowH / 100;
                        }
                    }

                    if (!isNaN(h)) {
                        h += 'px';
                    }

                    properties.height = h;
                }

                Modal.create({
                    body: $iframe,
                    title: $link.attr('title') || $link.text(),
                })
                .then(function(modal) {

                    // When the dialog is closed, pause video and audio.
                    modal.getRoot().on(ModalEvents.hidden, function() {
                        $iframe.contents().find('video, audio').each(function() {
                            this.pause();
                        });
                    });

                    modal.getRoot().find('> .modal-dialog').attr('style', 'width: ' + properties.width +
                                                                    '; height: ' + properties.height + ';')
                                                                    .addClass('bbco-modal-dialog');
                    modal.show();
                    $link.data('dialogue', modal);

                    return modal;
                })
                .catch(function(e) {
                    Log.debug('Error creating modal');
                    Log.debug(e);
                });

            } else {
                dialogue.show();
            }
        });
    });
};
