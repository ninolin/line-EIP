<?php

return [

    'channel' => env('LINE_CHANNEL') ? explode(',', env('LINE_CHANNEL')) : []

];
