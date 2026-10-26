<?php
// An old PHP file on disk is not automatically an HTTP endpoint.
require_once __DIR__ . '/app/http.php';
dispatch_public_request();
