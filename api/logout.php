<?php
/**
 * api/logout.php
 * POST — تسجيل الخروج
 */
require_once __DIR__ . '/../db/config.php';

header('Content-Type: application/json; charset=utf-8');

session_destroy();

jsonResponse(true, 'You have been logged out.');
