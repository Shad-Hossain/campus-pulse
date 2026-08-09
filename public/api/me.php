<?php
require_once __DIR__ . '/../../includes/helpers.php';
$user = current_user();
if (!$user) json_out(['user' => null]);
json_out(['user' => $user]);
