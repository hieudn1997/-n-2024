<?php

return [
    'id' => env('BANK_ID'),
    'username' => env('BANK_USERNAME'),
    'password' => env('BANK_PASSWORD'),
    'secret_code' => env('BANK_SECRET_CODE'),
    'name' => env('BANK_NAME'),
    'code' => env('BANK_CODE'),
    'account_number' => env('BANK_ACCOUNT_NUMBER'),
    'accountant_holder' => env('BANK_ACCOUNTANT_HOLDER'),
    'login_url' => env('BANK_DOMAIN') . env('BANK_LOGIN'),
    'get_history_url' => env('BANK_DOMAIN') . env('BANK_HISTORY'),
    'external_account_info_url' => env('BANK_DOMAIN') . env('BANK_EXTERNAL_ACCOUNT_INFO'),
    'internal_account_info_url' => env('BANK_DOMAIN') . env('BANK_INTERNAL_ACCOUNT_INFO'),
];
