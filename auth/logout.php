<?php
session_start();
require_once __DIR__ . '/../config/auth.php';

logout();
setFlashMessage('success', 'You have been logged out successfully.');
redirect('../index.php');

