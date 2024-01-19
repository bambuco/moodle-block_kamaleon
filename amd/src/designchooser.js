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
 * Source type selection handler.
 *
 * @module    block_kamaleon/typechooser
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialise the type chooser.
 */
export const init = () => {
    document.querySelector('#id_design').addEventListener("change", e => {
        var form = e.target.closest("form");
        // Add a hidden input to the form with the preview param.
        var input = document.createElement("input");
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "preview");
        input.setAttribute("value", "1");
        form.appendChild(input);

        // Submit the form.
        form.querySelector('#id_submitbutton').click();
    });
};
