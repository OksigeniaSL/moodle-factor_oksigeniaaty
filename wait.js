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
 * The sign-in screen waits for the phone: it asks every two seconds, goes on by itself when the phone chose the
 * number, and shows the page again when the request ends some other way (stopped, expired, replaced by another).
 *
 * A plain script, not an AMD module: it runs before the factor is passed, on the oldest browsers of a school.
 *
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
(function() {
    // Without a request to wait for (too many, not sent, expired), the form's button has nothing to confirm.
    var still = document.querySelector('[data-oksigeniaaty-nosubmit]');
    if (still) {
        var form0 = still.closest ? still.closest('form') : null;
        var button0 = form0 ? form0.querySelector('[name="submitbutton"]') : null;
        if (button0) {
            button0.hidden = true;
        }
        return;
    }

    var box = document.querySelector('[data-oksigeniaaty-check]');
    if (!box || !window.fetch) {
        return;
    }
    var url = box.getAttribute('data-oksigeniaaty-check');
    var form = box.closest ? box.closest('form') : null;
    var done = false;

    // The form's own button («I have approved it») is only a way out if the screen does not move on by itself:
    // it does not look like the main thing to do, which is on the phone.
    var button = form ? form.querySelector('[name="submitbutton"]') : null;
    if (button && button.classList) {
        button.classList.remove('btn-primary');
        button.classList.add('btn-outline-secondary');
    }

    // «Not getting it? Send another notice», once there has been time for it to arrive.
    var resend = box.querySelector('[data-oksigeniaaty-resend]');
    if (resend) {
        window.setTimeout(function() {
            resend.hidden = false;
        }, 20000);
    }

    var goOn = function() {
        if (button) {
            button.click();
        } else if (form) {
            form.submit();
        }
    };

    var tick = function() {
        window.fetch(url, {credentials: 'same-origin', cache: 'no-store'}).then(function(response) {
            return response.json();
        }).then(function(data) {
            if (done) {
                return;
            }
            if (data.status === 'pending') {
                window.setTimeout(tick, 2000);
                return;
            }
            done = true;
            if (data.status === 'approved') {
                goOn();
            } else {
                window.location.reload();
            }
        }).catch(function() {
            window.setTimeout(tick, 5000);
        });
    };

    window.setTimeout(tick, 2000);
})();
