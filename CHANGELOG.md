# Changelog

## 0.2.12-dev (27 November 2026)

- The notices of a new phone, of the recovery codes and of the help of the family go by mail in the common look of the
  organization, by the way of Oksigenia Marandu 0.70 or later when it is there: its name, colour and logo inside the
  mail, a greeting, a button to «Your accesses» (or to the code, for the adult asked to help), why it arrives and a text
  version. What the bell and the app show does not change, and without Marandu they go as before.

## 0.2.11-dev (23 November 2026)

- The check of GitHub gives write permission to the plugin's git files after the checkout: git now writes the reverse
  indexes of its packs read-only, and «grunt» of moodle-plugin-ci could not put back its copy of the plugin over them.

## 0.2.10-dev (22 November 2026)

- Help of the family, for someone without their phone and without their recovery codes (with Oksigenia Family): «No
  phone or codes? Ask an adult of your family for help» on the sign-in screen tells every other adult of their household
  with an account. The notice leads to a page, behind that adult's own sign-in, with a code of six digits to tell them in
  person or on the phone, as Apple's recovery contacts do: the code never goes to whoever asks. It works once, for ten
  minutes, and only in the browser that asked. «They did not ask me» stops it, ends that browser's session and blocks
  asking again for a day; no more than three a day. The person is told when help is asked for, used or stopped.
  «Your accesses» names who can help. A setting turns it off; without Oksigenia Family it does not show.
- Its design: the helper's page fits a phone without scrolling (who asks, the code large with the time left, and «They
  did not ask me» with the warning, the only red edge on the page), and each other state in a calm card of its own.

## 0.2.9-dev (22 November 2026)

- Recovery codes, for the day the phone is lost or out of battery: ten codes of eight digits, made from «Your accesses»
  and shown once on a page to print. Each one passes the factor once: the sign-in screen offers «No phone? Use a
  recovery code» below the number, open from the start when no phone or chat can be reached. A wrong code counts a
  failure, as tool_mfa does with any other. Only an HMAC of each code is kept; new codes replace the old ones, and
  making them or using one is told to the person (a new notice, `recovery`, by email too).
- With recovery codes left, the factor can be passed even when nothing can reach the person; resetting the factor for
  someone takes their codes too.
- «Your accesses» gets its design: the state on top in a line (what approves, and a calm amber note with «Change your
  password» when a sign-in was stopped), what approves on one side and the sign-ins by day on the other, every date in
  words («today at 08:14», «on 19 November»). The recovery codes show as a ticket of ten; the printed sheet fits on one
  page, with a box to tick each code. The sign-in screen's code box shows the shape of a code.
- A phone's «last approved» is only set by its own signed answer: passing with a recovery code or a chat no longer
  marks every phone of the person as used.

## 0.2.8-dev (22 November 2026)

- «Your accesses» (`accesses.php`): the phones and chats that approve the person's sign-ins, each with a button to
  remove it (a phone, with a confirmation and tool_mfa's own revoke and event), and the sign-ins that asked for approval
  in the last 30 days, with the browser, what happened and where it was answered. Each one is someone who typed the
  password, so the list ends with «Change my password». The requests are now kept 30 days instead of one; the codes to
  register a phone, still one day.
- It is reached from the card of the multi-factor preferences (which now says what approves, «1 phone and 2 chats»),
  from the setup page and from the notice of a new phone.
- The strings tool_mfa asks every factor for and this one lacked (`managefactor`, `manageinfo`, `action:manage`,
  `setupfactor`, `revokefactorconfirmation`): the preferences showed `[[manageinfo]]` once a phone was registered.

## 0.2.7-dev (22 November 2026)

- The site's administrators type the number on the phone instead of choosing it among three, which is guessed one
  time in three (`request::typed()`; number matching, as NIST SP 800-63B asks): the sign-in screen says «On your phone,
  type this number», and the phone, in the app and on the web, asks for it with its own warning. A chat cannot take a
  typed number: to them it only says to open the app, and keeps «It was not me» (a new notice, `login_check_typed`,
  switched on in each channel where `login_check` is).

## 0.2.6-dev (22 November 2026)

The 0.2.5-dev, only in dev and beta, had a first wording of the warning that repeated «if it is you».

- On the phone (the app, the web and the chats): «Nobody at the centre will ask you to tap a number. If someone does,
  tap «Not me».», against the call that says «you will get a notice, tap the 47».
- The time left: on the phone, as the page opens («2 minutes left to answer»); on the sign-in screen, counted down every
  second, and said to a screen reader at two minutes, one and thirty seconds, not every second.

## 0.2.4-dev (22 November 2026)

- Its notices in the app carry the plugin's icon instead of the app's generic pencil: the app draws its own icon only
  beside notices of activities, and takes anyone else's from the notice.

## 0.2.3-dev (22 November 2026)

- The code to join a chat lives as long as the page's QR (10 minutes), and a new one cancels the one before
  (Marandu 0.51.1).
- The setup page lists the chats that can approve, with «Take away» for one that is not the person's.
- No more than 6 requests in an hour, besides 3 in a quarter of an hour.

## 0.2.2-dev (22 November 2026)

- `api::account_reassigned()`: an account given to another person loses the phones its former owner registered,
  and nothing of theirs stays waiting.

## 0.2.1-dev (22 November 2026)

- The phone's page of a stopped sign-in no longer says «Sign-in stopped» twice.

## 0.2.0-dev (22 November 2026)

- **Security: a phone approves only once registered, with a key of its own.** Until 0.1.5, whoever had a person's
  password could get a token of the app (which does not go through multi-factor authentication), and with it answer
  the request from any phone, or join a Telegram chat of their own and answer there. Now a phone is registered from
  the multi-factor preferences, which wait for another factor: the app reads a QR there and gets a key that only it
  and the site know, and every answer goes signed with it. Chats count only when joined with a link of that same
  page, and only with an Oksigenia Marandu that can tell them apart (its `OWNJOIN`); with an older one, the factor
  does not ask on chats. On upgrade, the empty rows of the factor are removed and the waiting requests expire.
- A sign-in stopped from the phone fails at once on any page, ends that browser's session, counts a failure, and
  holds back new requests for 30 minutes (a day after three); at most three requests in a quarter of an hour.
- The person is told, by email too, when a new phone can approve their sign-ins.
- A page of its own for the stopped sign-in; clearer screens: three numbers announced, the wait first, another
  notice at hand, each state with one thing to do, and the outcomes titled on the phone.
- Requests and codes older than a day are removed every hour.

## 0.1.5-dev (21 November 2026)

- A sign-in in the window of the Moodle app does not send the notice to the app, which would open the numbers under
  the window, out of sight: there the factor asks only on Telegram or WhatsApp, and does not offer itself to whoever
  has neither.

## 0.1.4-dev (21 November 2026)

- The text columns of the table have no empty default, which Moodle does not take.

## 0.1.3-dev (21 November 2026)

- The notice asks «Is it you?» in its own text, in the person's language; the number is centred on the sign-in
  screen, with what to do with it next to it; the line above says only what the factor does, also when
  there is no number to show.

## 0.1.0-dev (21 November 2026)

- First version: the sign-in screen shows a number, the phone gets a notice with three numbers and «It was not me»,
  and the screen goes on by itself when the right number is tapped. In the Moodle app, on the web and, with Oksigenia
  Marandu, on Telegram and WhatsApp.
