<?php

return [
    's3' => [
        'bucket' => [
            /*
             * Folder on bucket to save the file
             */
            'folder' => date('Y/m/d'),
        ],
        'presigned_url' => [
            /*
             * Expiration time of the presigned URLs
             */
            'expiry_time' => '+3 hour',
        ],
    ],
];
