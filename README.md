## Installation

Clone the repository and install dependencies:

```
git clone git@github.com:lodar/laravel-telegram-bot.git
cd laravel-telegram-bot
composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --no-suggest --optimize-autoloader
```

Edit `.env` config to set your database and run migration command:

```
php artisan migrate
```

Create your bot with [@BotFather bot](https://t.me/BotFather).

Insert your bot to database:

```
php artisan tinker --execute="\App\Bot::insert([ 
    'name' => 'myNewBot', 
    'callback' => 'secret_webhook_path',  // change it to random string
    'token' => '0000:XXXXXX',
    'owner' => '@groupname', // change it to telegram_user_id, channel_id or @group_name 
    ]);"
```

Insert bot steps:

```
php artisan tinker --execute="\App\Step::insert([ 
    'step_order' => 1,
    'message' => 'Hello?',
    'payload' => '', // optional json payload
    'skippable' => 0, 
    'bot_id' => 1,
    ]);"
```


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

