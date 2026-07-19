<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/helpers/Response.php';
require_once dirname(__DIR__, 2) . '/models/User.php';
require_once dirname(__DIR__) . '/../../security/middleware.php';

startSecureSession();
requireAuth();

$user = User::findById((int) $_SESSION['user_id']);
if ($user === null) {
    session_destroy();
    Response::error('User not found', 401);
}

Response::success(['user' => $user]);
