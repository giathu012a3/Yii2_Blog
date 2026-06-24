<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'cacheTTL' => [
        'post' => ((int)$_ENV['CACHE_TTL_POST'] ?? 3600),
        'category' => ((int)$_ENV['CACHE_TTL_CATEGORY'] ?? 86400),
        'tag' => ((int)$_ENV['CACHE_TTL_TAG'] ?? 86400)
    ]
];
