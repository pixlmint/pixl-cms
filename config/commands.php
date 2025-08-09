<?php

use PixlMint\CMS\Command\WebRequestCommand;

return [
    'root' => [
        [
            'spec' => 'h|help',
            'descr' => 'Show this help message',
        ],
    ],
    'subcommands' => [
        'web' => [
            'request' => [
                'descr' => 'Send a request to the given path',
                'handler' => WebRequestCommand::class,
                'args' => [
                    [
                        'spec' => 'p|path:',
                        'descr' => 'The path to request',
                        'type' => 'string',
                    ],
                    [
                        'spec' => 'm|method:',
                        'descr' => 'Http Method',
                        'default' => 'GET',
                        'type' => 'string',
                    ],
                ],
            ],
        ],
    ],
];
