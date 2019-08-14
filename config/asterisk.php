<?php

return [
    'remove_unreg'              => env( 'ASTERISK_REMOVE_UNREG', false ),
    'external_ip'               => '109.206.159.155',
    'ip'				        => '10.10.10.155',
    'port'                      => 5038,
    'user'                      => 'asterisk',
    'pass'                      => 'ololoasterisk321123',
    'queue'                     => 'eds-zhuk',
    'context'                   => 'default',
    'channel_mask'              => '{{prefix}}{{number}}{{postfix}}',
    'channel_prefix'            => 'SIP/',
    'channel_postfix'           => '',
    'channel_postfix_trunc'     => '@m9295070506',
    'tolerance'                 => 5, // погрешность разницы во времени (сек)
    'period_hours'              => 3, // cdr за последние n часов
    'redirect_timeout'          => 0, // редирект звонка через (сек)
    'allowed'                   => [ // разрешенные каналы
        'SIP/84956483888*',
        'SIP/00001*',
        'SIP/00002*',
        //'SIP/m9266483888*',
        //'SIP/m9296483888*',
        //'SIP/m9265193320*'
    ]
];
