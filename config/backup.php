<?php

return [
    'retention_days' => env('BACKUP_RETENTION_DAYS', 30),
    'drive_token' => env('BACKUP_DRIVE_TOKEN'),
    'onedrive_token' => env('BACKUP_ONEDRIVE_TOKEN'),
    'storage_path' => storage_path('app/backups'),
];
