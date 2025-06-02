<?php

return [
    /*
     * Default paths for generated files.
     */
    'paths' => [
        'models' => 'app/Models',
        'controllers' => 'app/Http/Controllers',
        'requests' => 'app/Http/Requests',
        'views' => 'resources/views',
        'routes' => 'routes/web.php',
        'migrations' => 'database/migrations',
    ],

    /*
     * Default stubs to use.
     * These stubs will be published to `resources/stubs/laraquickcrud`
     * and can be customized by the user.
     */
    'stubs' => [
        'controller' => 'laraquickcrud::controller',
        'model' => 'laraquickcrud::model',
        'request_store' => 'laraquickcrud::request_store',
        'request_update' => 'laraquickcrud::request_update',
        'view_index' => 'laraquickcrud::view_index',
        'view_create' => 'laraquickcrud::view_create',
        'view_edit' => 'laraquickcrud::view_edit',
        'view_show' => 'laraquickcrud::view_show',
        'migration' => 'laraquickcrud::migration',
        'route' => 'laraquickcrud::route',
    ],

    /*
     * Default namespace for generated files.
     */
    'namespaces' => [
        'models' => 'App\\Models',
        'controllers' => 'App\\Http\\Controllers',
        'requests' => 'App\\Http\\Requests',
    ],

    /*
     * Default primary key type for migrations.
     * Options: 'id' (increments), 'uuid' (string)
     */
    'primary_key_type' => 'id',

    /*
     * Custom replacements for stubs.
     * You can add your own placeholders and their default values here.
     * These will be available in your stubs for dynamic content replacement.
     */
    'replacements' => [
        '{{namespace}}' => null,
        '{{modelName}}' => null,
        '{{modelNamePlural}}' => null,
        '{{modelNameSingularLowercase}}' => null,
        '{{modelNamePluralLowercase}}' => null,
        '{{tableName}}' => null,
        '{{fields}}' => null, // Used for migration fields
        '{{fillable}}' => null, // Used for model fillable property
        '{{rules}}' => null, // Used for request validation rules
        '{{formFields}}' => null, // Used for view form fields
        '{{tableHeaders}}' => null, // Used for view table headers
        '{{tableRows}}' => null, // Used for view table rows
    ],
];