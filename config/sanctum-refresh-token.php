<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Refresh Route Names
    |--------------------------------------------------------------------------
    |
    | This value controls the used refresh route names
    |
    */
    'refresh_route_names' => 'api.token.refresh',

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued tokens will be
    | considered expired.
    |
    */
    'auth_token_expiration'    => 60,
    'refresh_token_expiration' => 180,
];
