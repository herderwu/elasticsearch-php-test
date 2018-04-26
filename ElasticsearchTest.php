<?php

// /my_index/my_type/_search?pretty&q=title:one&from=1&size=1&terminate_after=1"

use Elasticsearch\ClientBuilder;

require 'vendor/autoload.php';

require 'config.php';

$client = ClientBuilder::create()           // Instantiate a new ClientBuilder
                    ->setHosts($hosts)      // Set the hosts
                    ->setRetries(3)
                    ->build();              // Build the client object

// 查询
$params = [
    'index' => 'my_index',
    'type' => 'my_type',
    "size" => 3,               // how many results *per shard* you want back
    "from" => 0,
    'client' => [
        'timeout' => 3,        // ten second timeout
        'connect_timeout' => 3
    ],
    "_source_include" => [ "title"],
    'body' => [
        'query' => [
            'match' => [
                'title' => '国'
            ]
        ]
    ]
];

$response = $client->search($params);
print_r($response);


// 条数
$params = [
    'index' => 'my_index',
    'type' => 'my_type',
    'client' => [
        'timeout' => 3,        // ten second timeout
        'connect_timeout' => 3
    ],
    'body' => [
        'query' => [
            'match' => [
                'title' => '国'
            ]
        ]
    ]
];
$response = $client->count($params);
print_r($response);

exit;

// 导入
$params = ['body' => []];

for ($i = 1; $i <= 100; $i++) {
    $params['body'][] = [
        'index' => [
            '_index' => 'my_index',
            '_type' => 'my_type',
            '_id' => $i
        ]
    ];

    $params['body'][] = [
        'title' => 'my_国_value' . $i,
        'second_field' => 'some more values'
    ];

    // Every 1000 documents stop and send the bulk request
    if ($i % 10 == 0) {
        $responses = $client->bulk($params);

        // erase the old bulk request
        $params = ['body' => []];

        // unset the bulk response when you are done to save memory
        unset($responses);
    }
}

// Send the last batch if it exists
if (!empty($params['body'])) {
    $responses = $client->bulk($params);
}
