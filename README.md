# Approve from the phone (factor_oksigeniaaty)

A second factor for Moodle's multi-factor authentication (`tool_mfa`): each sign-in is approved from a notice on the
phone.

1. After the password, the sign-in screen shows a number, for example **42**.
2. The phone gets a notice: «Is it you? Signing in to *your site*: Chrome on Windows, at 9:41».
3. The notice opens three numbers and «It was not me». Tapping **42** lets the sign-in go on; the screen moves on by
   itself, with no code to type.
4. Any other answer stops the sign-in. So does a hurried tap on the wrong number: approving by accident lets nobody in.
   After «It was not me», the phone offers to change the password.

## Where the notice arrives

- **In the Moodle app**, and in apps built from it, on a phone that gets the site's push notifications. The notice
  opens a page of the app that answers with the app's own session.
- **On Telegram and WhatsApp**, with [Oksigenia Marandu](https://github.com/OksigeniaSL/moodle-local_oksigeniamarandu)
  installed: the same question, with a button for each number. On WhatsApp, only inside the 24 hours of a conversation.
- **On the web**, from the link of the notice, in another browser where the person is already signed in.

When the sign-in happens in the window of the Moodle app itself, the app's notice would open the numbers under that
window, out of sight: there the factor asks only on Telegram or WhatsApp, which are other apps.

## Registering the phone

A phone approves only once registered, with a key of its own:

1. In the multi-factor preferences (which wait for another factor, such as the email code), «Approve from the phone»
   shows a QR.
2. The app reads it (or the phone's camera, and then «Open in the app»), and gets a key that only the app and the site
   know. The person is told, by email too, that a new phone can approve their sign-ins.
3. Every answer goes signed with that key. A token of the app, which the password alone gives, is not enough.

A Telegram or WhatsApp chat approves only if joined with a link of that same page. A lost phone is removed from the
same preferences.

The factor offers itself to whoever has a registered phone or chat, and stays neutral for everyone else.

## Safety

- A request belongs to the browser session that is signing in: only that browser goes on with it.
- The phone answers as the person: a request of anyone else is not found. One answer closes it.
- A request lasts three minutes by default (setting «Time to answer»); a new one replaces the one waiting; at most three
  in a quarter of an hour.
- A sign-in stopped from the phone ends that browser's session, counts a failure, and holds back new requests for 30
  minutes (a day after three).
- The page that answers on the web waits for the factor like any other, so a browser that is still signing in cannot
  approve itself.
- Requests are kept for a day.

## Requirements

Moodle 4.5 to 5.3, with multi-factor authentication on, and the site's push notifications (Airnotifier) for the app.

## Licence

GNU GPL v3 or later. © 2026 Oksigenia SL.
