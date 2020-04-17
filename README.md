## Install on heroku
  
Install [heroku-cli](https://devcenter.heroku.com/articles/heroku-cli).  
  
Clone the repository and install dependencies.  

```
git clone git@github.com:lodar/laravel-telegram-bot.git
cd laravel-telegram-bot
heroku create
git add . && git commit -am 'release' &&  git push heroku master
heroku run composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader
```
  

Connect to [heroku postgres](https://elements.heroku.com/addons/heroku-postgresql) to your app.  
Edit `.env` DB_* config values to set your database and run migration command.  

```
heroku config:set DB_CONNECTION=pgsql DB_HOST= DB_PORT=5432 DB_DATABASE= DB_USERNAME= DB_PASSWORD=
php artisan migrate --force
```
  
  
Create your bot with [@BotFather bot](https://t.me/BotFather) and copy bot api `token`.  
  
Insert your bot to database. Change `callback` to random string for telegram webhook.  
After conversation is complete, chat log will be sent to the `owner`.  
Change `owner` to telegram user_id, channel_id or group_id.  
To find group_id invite `@RawDataBot` to your group. [More info.](https://stackoverflow.com/a/46247058)  
Don't forget to kick `@RawDataBot` from your group and invite your new bot.  
  
```
heroku run php artisan tinker --execute="\App\Bot::insert([ 
    'name' => 'myNewBot', 
    'callback' => 'secret_webhook_path',
    'token' => '0000:XXXXXX',
    'owner' => '@groupname',
    ]);"
```
  
  
Attach a webhook to your new bot:  
  
```
TOKEN=0000:XXXX # your bot token
CALLBACK=secret_webhook_path # your secret callback path
curl -s -X POST 'https://api.telegram.org/bot'$TOKEN'/setWebhook' -d  \
"url=https://your-domain/callback/"$CALLBACK 
```
  
  
Insert bot steps.   
`step_order` - start with 1, incremental.  
`payload` - add custom action buttons to the step or leave as null.  
`skippable` - step can be skipped.  
`uploadable` - step can handle file upload by user.  
  
```
heroku run php artisan tinker --execute="\App\Step::insert([ 
    'step_order' => 1, 
    'message' => 'Hello?',
    'payload' => '[
    {
        "text": "Skip step",
        "callback_data": "Skip step"
    },
    {
        "text": "Start over",
        "callback_data": "/start"
    }
]', 
    'skippable' => 0, 
    'uploadable' => 0,
    'bot_id' => 1, 
    ]);"
```
  
All set! Now start conversation with your bot with `/start`.  
  
  
## About Laravel

Laravel is a web application framework with expressive, elegant syntax. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

