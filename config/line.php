<?php

return [

    'channel' => env('LINE_CHANNEL') ? explode(',', env('LINE_CHANNEL')) : [],
    'channel_token' => env('LINE_CHANNEL_ACCESS_TOKEN')

];
