<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Canonical repository tree
    |--------------------------------------------------------------------------
    |
    | Four structural roots live under every YearFolder. Child folders must
    | nest under those roots (parent_id), never as siblings on the year page.
    | Source of truth: App\Support\CooperationFolderTaxonomy.
    |
    */
    'max_folder_depth' => 10,

];
