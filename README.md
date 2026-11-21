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

Nothing to set up. The factor offers itself to whoever can get the notice, and stays neutral for everyone else.

## Safety

- A request belongs to the browser session that is signing in: only that browser goes on with it.
- The phone answers as the person: a request of anyone else is not found. One answer closes it.
- A request lasts three minutes by default (setting «Time to answer»); a new one replaces the one waiting; at most five
  in a quarter of an hour.
- The page that answers on the web waits for the factor like any other, so a browser that is still signing in cannot
  approve itself.
- Requests are kept for a day.

## Requirements

Moodle 4.5 to 5.3, with multi-factor authentication on, and the site's push notifications (Airnotifier) for the app.

## Licence

GNU GPL v3 or later. © 2026 Oksigenia SL.
