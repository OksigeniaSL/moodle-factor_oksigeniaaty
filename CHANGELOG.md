# Changelog

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
