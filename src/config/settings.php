<?php
return [
    // -- Cache Filename
    'cache_file' => storage_path('settings.json'),

    // -- Table name to store settings
    'db_table'   => 'settings',

    // -- Setting key field name
    'db_field_key'   => 'setting_key',

    // -- Setting value field name
    'db_field_value'   => 'setting_value',

    // -- Fallback setting
    'fallback'   => true
];
