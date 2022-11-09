<?php

$user_signed_in = [ 
    "action" => "user.signed_in",
    "occurred_at" => date("c"),
    "version" => 1,
    "actor" => [
        "id" => "user_01GBNJC3MX9ZZJW1FSTF4C5938",
        "type" => "user",
    ],
    "targets" => [
        [
            "id" => "team_01GBNJD4MKHVKJGEWK42JNMBGS",
            "type" => "team",
        ],
    ],
    "context" => [
        "location" => "123.123.123.123",
        "user_agent" => "Chrome/104.0.0.0",
    ],
];

$user_logged_out = [
    "action" => "user.logged_out",
    "occurred_at" => date("c"),
    "actor" => [
        "id" => "user_01GBNJC3MX9ZZJW1FSTF4C5938",
        "type" => "user",
    ],
    "targets" => [
        [
            "id" => "team_01GBNJD4MKHVKJGEWK42JNMBGS",
            "type" => "team",
        ],
    ],
    "context" => [
        "location" => "123.123.123.123",
        "user_agent" => "Chrome/104.0.0.0",
    ],
];

$user_organization_set = [
    "action" => "user.organization_set",
    "occurred_at" => date("c"),
    "actor" => [
        "id" => "user_01GBNJC3MX9ZZJW1FSTF4C5938",
        "type" => "user",
    ],
    "targets" => [
        [
            "id" => "team_01GBNJD4MKHVKJGEWK42JNMBGS",
            "type" => "team",
        ],
    ],
    "context" => [
        "location" => "123.123.123.123",
        "user_agent" => "Chrome/104.0.0.0",
    ],
];

$user_organization_deleted = [
    "action" => "user.organization_deleted",
    "occurred_at" => date("c"),
    "actor" => [
        "id" => "user_01GBNJC3MX9ZZJW1FSTF4C5938",
        "type" => "user",
    ],
    "targets" => [
        [
            "id" => "team_01GBNJD4MKHVKJGEWK42JNMBGS",
            "type" => "team",
        ],
    ],
    "context" => [
        "location" => "123.123.123.123",
        "user_agent" => "Chrome/104.0.0.0",
    ],
];

$user_connection_deleted = [
    "action" => "user.connection_deleted",
    "occurred_at" => date("c"),
    "actor" => [
        "id" => "user_01GBNJC3MX9ZZJW1FSTF4C5938",
        "type" => "user",
    ],
    "targets" => [
        [
            "id" => "team_01GBNJD4MKHVKJGEWK42JNMBGS",
            "type" => "team",
        ],
    ],
    "context" => [
        "location" => "123.123.123.123",
        "user_agent" => "Chrome/104.0.0.0",
    ],
];
