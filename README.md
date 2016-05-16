# slack-php-bot
A shitty Slack bot made in PHP that abused webhooks!

## Installation
Clone this repo on any folder that is public accesible to the internet (it's a php script, duh).

On your slack team settings, create a new outgoing webhook and configure it and add the token to the configuration in `slack.php`.

Create your custom commands (see `$commandData` reference [here](https://github.com/Repflez/slack-php-bot/blob/master/slack.php#L108-L115)). and add them to your script.

Do your command and get output!

A basic command is included in `slack.php`that you can call with `!welcome`.
