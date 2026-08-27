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
 * Flexslider implementation.
 *
 * @module    block_kamaleon/horizontalslider
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Include the FlexSlider and create an horizontal slider library.
 */
define(['./flexslider'], function() {
    'use strict';

    /**
     * Recalculate the final translation to include both track edges.
     *
     * @param {object} slider The FlexSlider instance.
     * @param {number} edgePadding Symmetric padding at the start and end of the track.
     */
    const updateGeometry = function(slider, edgePadding) {
        slider.doMath();

        const appliedEdgePadding = Math.min(edgePadding, Math.max((slider.w - slider.itemW) / 2, 0));
        slider.container.css({
            paddingLeft: appliedEdgePadding,
            paddingRight: appliedEdgePadding,
        });

        const availableWidth = Math.max(slider.w - (appliedEdgePadding * 2), slider.itemW);
        slider.visible = Math.max(1, Math.floor(
            (availableWidth + slider.vars.itemMargin) / (slider.itemW + slider.vars.itemMargin)
        ));
        slider.move = slider.vars.move > 0 && slider.vars.move < slider.visible ?
            slider.vars.move : slider.visible;
        slider.pagingCount = Math.max(1, Math.ceil(
            ((slider.count - slider.visible) / slider.move) + 1
        ));
        slider.last = slider.pagingCount - 1;

        slider.currentSlide = Math.min(slider.currentSlide, slider.last);
        slider.animatingTo = Math.min(slider.animatingTo, slider.last);
        slider.atEnd = slider.currentSlide === 0 || slider.currentSlide === slider.last;

        const contentWidth = (slider.itemW * slider.count) +
            (slider.vars.itemMargin * Math.max(slider.count - 1, 0)) +
            (appliedEdgePadding * 2);

        slider.limit = Math.max(contentWidth - slider.w, 0);
        slider.slides.width(slider.computedW);
        slider.setProps();
    };

    return {
        init: function($element, properties) {
            const edgePadding = properties.edgePadding || 0;
            const originalInit = properties.init;
            const originalStart = properties.start;
            delete properties.edgePadding;

            const $slides = $element.find(properties.selector);
            $slides.parent().css({
                paddingLeft: edgePadding,
                paddingRight: edgePadding,
            });

            properties.init = function(slider) {
                updateGeometry(slider, edgePadding);

                if (typeof originalInit === 'function') {
                    originalInit(slider);
                }
            };

            properties.start = function(slider) {
                updateGeometry(slider, edgePadding);

                if (typeof window.ResizeObserver !== 'undefined') {
                    const observer = new window.ResizeObserver(function() {
                        if (!slider.animating) {
                            updateGeometry(slider, edgePadding);
                        }
                    });
                    observer.observe($element[0]);
                    slider.kamaleonResizeObserver = observer;
                }

                if (typeof originalStart === 'function') {
                    originalStart(slider);
                }
            };

            $element.flexslider(properties);
        }
    };

});
