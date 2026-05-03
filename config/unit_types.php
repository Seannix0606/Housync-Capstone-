<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default bedroom count by unit type (canonical slug keys)
    |--------------------------------------------------------------------------
    |
    | Keys match option values in landlord unit forms. Extend this array when
    | adding new unit types; frontend autofill and validation read from here.
    |
    */
    'default_bedrooms_by_type' => [
        'studio' => 0,
        'one_bedroom' => 1,
        'two_bedroom' => 2,
        'three_bedroom' => 3,
        'penthouse' => 3,
    ],

];
