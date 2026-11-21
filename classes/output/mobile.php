<?php
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

namespace factor_oksigeniaaty\output;

use factor_oksigeniaaty\local\request;

/**
 * The factor in the Moodle app.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {
    /**
     * The link of the notice opens the page of the request instead of the site in a window. No entry in the menu.
     *
     * @param array $args What the app sends.
     * @return array
     */
    public static function mobile_init(array $args): array {
        return [
            'templates' => [],
            'javascript' => (string) file_get_contents(__DIR__ . '/../../app/links.js'),
            'otherdata' => '',
            'disabled' => true,
        ];
    }

    /** @var string The page's script: the key of this phone, kept by the app per account, and the signed answers. */
    private const KEYS_JS = <<<'JS'
var self = this;
var site = this.CoreSitesProvider.getCurrentSite();
var storekey = 'factor_oksigeniaaty_' + site.getId();
var stored = function () {
    try {
        return JSON.parse(window.localStorage.getItem(storekey) || 'null');
    } catch (e) {
        return null;
    }
};
var tobase64 = function (bytes) {
    var text = '';
    for (var i = 0; i < bytes.length; i++) {
        text += String.fromCharCode(bytes[i]);
    }
    return window.btoa(text);
};
var frombase64 = function (text) {
    var raw = window.atob(text);
    var bytes = new Uint8Array(raw.length);
    for (var i = 0; i < raw.length; i++) {
        bytes[i] = raw.charCodeAt(i);
    }
    return bytes;
};
this.oksigeniaatyEnrolled = !!stored();
this.oksigeniaatyBusy = false;
this.oksigeniaatyFailed = false;
this.oksigeniaatyAnswer = function (requestid, choice) {
    var keys = stored();
    var subtle = window.crypto && window.crypto.subtle;
    if (!keys || !subtle) {
        self.oksigeniaatyEnrolled = false;
        return;
    }
    self.oksigeniaatyBusy = true;
    self.oksigeniaatyFailed = false;
    return subtle.importKey('raw', frombase64(keys.secret), {name: 'HMAC', hash: 'SHA-256'}, false, ['sign'])
        .then(function (key) {
            return subtle.sign('HMAC', key, new TextEncoder().encode(requestid + ':' + choice));
        })
        .then(function (signature) {
            return site.write('factor_oksigeniaaty_answer', {
                requestid: requestid,
                choice: String(choice),
                device: keys.device,
                proof: tobase64(new Uint8Array(signature)),
            }, {getFromCache: false, saveToCache: false});
        })
        .then(function (result) {
            if (result && result.status === 'notenrolled') {
                window.localStorage.removeItem(storekey);
                self.oksigeniaatyEnrolled = false;
                return;
            }
            return self.refreshContent(false);
        })
        .catch(function () {
            self.oksigeniaatyFailed = true;
        })
        .finally(function () {
            self.oksigeniaatyBusy = false;
        });
};
this.oksigeniaatyEnrol = function () {
    var code = self.CONTENT_OTHERDATA.code;
    var keys = stored();
    if (keys && keys.code === code) {
        self.oksigeniaatyState = 'done';
        return;
    }
    self.oksigeniaatyState = 'working';
    var label = window.device && window.device.model ? String(window.device.model) : '';
    return site.write('factor_oksigeniaaty_enrol', {code: code, label: label}, {getFromCache: false, saveToCache: false})
        .then(function (result) {
            if (result && result.registered) {
                window.localStorage.setItem(storekey, JSON.stringify({device: result.device, secret: result.secret, code: code}));
                self.oksigeniaatyEnrolled = true;
                self.oksigeniaatyState = 'done';
            } else {
                self.oksigeniaatyState = 'failed';
            }
        })
        .catch(function () {
            self.oksigeniaatyState = 'failed';
        });
};
if (this.CONTENT_OTHERDATA && this.CONTENT_OTHERDATA.code) {
    this.oksigeniaatyEnrol();
}
JS;

    /**
     * The request: who is signing in, from where, the three numbers and «It was not me»; or what happened to it. The
     * answer goes signed with this phone's key; a phone without it is told how to register.
     *
     * @param array $args The request (r), from the notice's link; without it, the one waiting.
     * @return array
     */
    public static function mobile_approve_view(array $args): array {
        global $OUTPUT, $USER;
        $id = (int) ($args['r'] ?? 0);
        $request = $id ? request::get($id, (int) $USER->id) : request::latest((int) $USER->id);
        return [
            'templates' => [[
                'id' => 'main',
                'html' => $OUTPUT->render_from_template(
                    'factor_oksigeniaaty/mobile_approve',
                    approval::context($request, $USER, false)
                ),
            ]],
            'javascript' => self::KEYS_JS,
            'otherdata' => ['code' => ''],
        ];
    }

    /**
     * Register this phone with the code of the setup page: the app gets its key and keeps it.
     *
     * @param array $args The code (c), from the link or the QR.
     * @return array
     */
    public static function mobile_enrol_view(array $args): array {
        global $OUTPUT;
        return [
            'templates' => [[
                'id' => 'main',
                'html' => $OUTPUT->render_from_template('factor_oksigeniaaty/mobile_enrol', []),
            ]],
            'javascript' => self::KEYS_JS,
            'otherdata' => ['code' => clean_param((string) ($args['c'] ?? ''), PARAM_ALPHANUM)],
        ];
    }
}
