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
 * In the app, the link of the notice opens the page of the request, answered with the app's own session and this
 * phone's key, instead of the site in a window that would ask for the factor again; and the setup page's link
 * registers this phone.
 *
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const that = this;

class OksigeniaAtyApproveLinks extends that.CoreContentLinksHandlerBase {
    constructor() {
        super();
        this.name = 'factor_oksigeniaaty_approve';
        this.pattern = /\/admin\/tool\/mfa\/factor\/oksigeniaaty\/approve\.php/;
    }

    getActions(siteIds, url, params) {
        const args = {r: parseInt(params.r || '0', 10)};
        return [{
            action: (siteId) => that.CoreNavigatorService.navigateToSitePath(
                'siteplugins/content/factor_oksigeniaaty/mobile_approve_view/' + that.Md5.hashAsciiStr(JSON.stringify(args)),
                {
                    params: {title: that.TranslateService.instant('plugin.factor_oksigeniaaty.mobile:title'), args},
                    siteId,
                }
            ),
        }];
    }
}

that.CoreContentLinksDelegate.registerHandler(new OksigeniaAtyApproveLinks());

// The setup page's QR or link: this phone registers itself, with the app's own session.
class OksigeniaAtyEnrolLinks extends that.CoreContentLinksHandlerBase {
    constructor() {
        super();
        this.name = 'factor_oksigeniaaty_enrol';
        this.pattern = /\/admin\/tool\/mfa\/factor\/oksigeniaaty\/enrol\.php\?(?:[^#]*&)?c=[0-9a-zA-Z]+/;
    }

    getActions(siteIds, url, params) {
        const args = {c: String(params.c || '').replace(/[^0-9a-zA-Z]/g, '')};
        return [{
            action: (siteId) => that.CoreNavigatorService.navigateToSitePath(
                'siteplugins/content/factor_oksigeniaaty/mobile_enrol_view/' + that.Md5.hashAsciiStr(JSON.stringify(args)),
                {
                    params: {title: that.TranslateService.instant('plugin.factor_oksigeniaaty.enrol:title'), args},
                    siteId,
                }
            ),
        }];
    }
}

that.CoreContentLinksDelegate.registerHandler(new OksigeniaAtyEnrolLinks());
