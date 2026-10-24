<?php

/*
|--------------------------------------------------------------------------
| Active Frontend Theme
|--------------------------------------------------------------------------
|
| Controls which theme folder under resources/views/themes/ is used to
| render the public institute homepage (WebsiteController@index).
|
| Defaults to "theme-one" so installing this file changes NOTHING until
| you deliberately opt in. To activate the new design, add this line to
| your .env file:
|
|     ACTIVE_FRONTEND_THEME=theme-three
|
| To roll back instantly, remove that line (or set it back to theme-one)
| and clear config cache: php artisan config:clear
|
*/

return [
    'active_theme' => env('ACTIVE_FRONTEND_THEME', 'theme-one'),
];
