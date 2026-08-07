<?php

return [

    'max_upload_mb' => (int) env('DOCUMENT_MAX_UPLOAD_MB', 20),

    'preview_max_mb' => (int) env('DOCUMENT_PREVIEW_MAX_MB', 5),

    'allowed_extensions' => array_values(array_filter(array_map(
        'trim',
        explode(',', strtolower((string) env(
            'DOCUMENT_ALLOWED_EXTENSIONS',
            'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png'
        )))
    ))),

    'allowed_mimes' => [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
    ],

    'blocked_extensions' => [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'sh', 'bash', 'exe', 'bat', 'cmd', 'com', 'msi',
        'js', 'mjs', 'cjs', 'vbs', 'ps1', 'cgi', 'pl', 'py', 'rb',
        'htaccess', 'html', 'htm', 'shtml',
    ],

    'preview_allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp'],

    'private_disk' => 'private_docs',

    'public_preview_disk' => 'public',

    'preview_directory' => 'previews',

];
