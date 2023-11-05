<?php

return \Flarum\Database\Migration::addColumns('users', [
    'required_password_reset_at' => ['datetime', 'nullable' => true],
    'password_reset_at' => ['datetime', 'nullable' => true],
]);
