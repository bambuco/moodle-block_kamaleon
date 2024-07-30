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
