// This file is part of Moodle - https://moodle.org/
//
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * DataTables initialisation and filter management for the course manager table.
 *
 * DataTables and its Bootstrap 5 integration are loaded as local AMD modules
 * (report_coursemanager/datatables and report_coursemanager/datatables-bs5),
 * so no external CDN requests are made. This is fully compliant with Moodle's
 * Content Security Policy (script-src 'self').
 *
 * @module      report_coursemanager/coursetable
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'report_coursemanager/datatables',
    'report_coursemanager/datatables-bs5',
], function($) {
    'use strict';

    /**
     * Fix Moodle html_table: move all body <tr> into <tbody> before DataTables reads the DOM.
     *
     * Moodle's html_table renderer sometimes emits <tr> directly inside <table>
     * (outside any <tbody>). DataTables requires a proper <thead>/<tbody> structure.
     *
     * @param {HTMLElement} tableEl
     */
    var fixTbody = function(tableEl) {
        var thead = tableEl.querySelector('thead');
        var tbody = tableEl.querySelector('tbody');

        if (!tbody) {
            tbody = document.createElement('tbody');
            tableEl.appendChild(tbody);
        }

        var theadRows = thead ? Array.prototype.slice.call(thead.querySelectorAll('tr')) : [];
        var allRows   = Array.prototype.slice.call(tableEl.querySelectorAll('tr'));

        allRows.forEach(function(row) {
            if (theadRows.indexOf(row) === -1 && row.parentNode !== tbody) {
                tbody.appendChild(row);
            }
        });
    };

    /**
     * Initialise DataTables and wire up filter buttons + text search.
     *
     * @param {string} tableId  HTML id of the table element to enhance.
     * @param {Object} lang     Localised strings passed from PHP via js_call_amd.
     */
    var initTable = function(tableId, lang) {
        var tableEl = document.getElementById(tableId);
        if (!tableEl) {
            return;
        }

        // Fix <tbody> BEFORE DataTables reads the DOM.
        fixTbody(tableEl);

        // Bail gracefully if DataTables did not attach to this jQuery instance.
        if (!$.fn.dataTable) {
            return;
        }

        // Active filter token — 'filterrow' means "show all".
        var activeFilter = 'filterrow';

        // Custom search plugin: filter rows by their CSS class.
        $.fn.dataTable.ext.search.push(function(settings, _data, rowIndex) {
            if (settings.nTable.id !== tableId) {
                return true;
            }
            if (activeFilter === 'filterrow') {
                return true;
            }
            var tr = settings.aoData[rowIndex] && settings.aoData[rowIndex].nTr;
            if (!tr) {
                return true;
            }
            return tr.classList.contains(activeFilter);
        });

        // Initialise DataTables.
        var dt = $(tableEl).DataTable({
            // No ordering on recommendations and actions columns (last two).
            columnDefs: [
                {orderable: false, targets: -1},
                {orderable: false, targets: -2},
            ],
            stripeClasses: ['dt-odd', 'dt-even'],
            order: [[0, 'asc']],
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            dom:
                '<"d-flex align-items-center justify-content-between mb-3"' +
                    '<"text-muted small"l>' +
                    '<"text-muted small"i>' +
                '>rt' +
                '<"d-flex align-items-center justify-content-center mt-3"p>',
            language: {
                lengthMenu:   lang.lengthMenu,
                info:         lang.info,
                infoEmpty:    lang.infoEmpty,
                infoFiltered: lang.infoFiltered,
                zeroRecords:  lang.zeroRecords,
                paginate: {
                    first:    lang.first,
                    last:     lang.last,
                    next:     lang.next,
                    previous: lang.previous,
                },
            },
            drawCallback: function() {
                var tbl = document.getElementById(tableId);
                if (tbl) {
                    tbl.querySelectorAll('tbody td').forEach(function(td) {
                        td.style.removeProperty('background-color');
                    });
                    tbl.querySelectorAll('tbody tr').forEach(function(tr) {
                        tr.style.removeProperty('background-color');
                        var bgColor = tr.classList.contains('dt-odd') ? '#f5f5f5' : '#ffffff';
                        tr.querySelectorAll('td').forEach(function(td) {
                            td.style.backgroundColor = bgColor;
                        });
                    });
                }
            },
            initComplete: function() {
                $(this.api().table().node())
                    .addClass('admintable generaltable browse_courses w-100');
            },
        });

        // Filter buttons.
        document.querySelectorAll('.coursemanager-filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.coursemanager-filter-btn')
                    .forEach(function(b) { b.classList.remove('active'); });
                btn.classList.add('active');
                activeFilter = btn.dataset.filter || 'filterrow';
                dt.draw();
            });
        });

        // Text search (replaces legacy search_courses.js).
        var searchInput = document.getElementById('coursemanager-search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                dt.search(searchInput.value).draw();
            });
        }
    };

    return {
        /**
         * Entry point called by $PAGE->requires->js_call_amd().
         *
         * DataTables and its Bootstrap 5 styles are already available via the
         * AMD dependencies declared above — no dynamic script injection needed.
         *
         * @param {string} tableId  HTML id of the table element to enhance.
         * @param {Object} lang     Localised strings passed from PHP.
         */
        init: function(tableId, lang) {
            initTable(tableId, lang);
        },
    };
});
