<?php
// dashboard.php - Root dashboard redirector
require_once 'config.php';

// Redirect to index.php which serves as the homepage dashboard in the root site
header('Location: index.php');
exit;
