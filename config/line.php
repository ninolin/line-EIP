<?php

return [

    'channel' => env('LINE_CHANNEL') ? array_filter(explode(',', env('LINE_CHANNEL'))) : []

];
