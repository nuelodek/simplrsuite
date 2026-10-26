<?php
require_once __DIR__ . '/app/marketing.php';
render_landing(audience_for_host($_SERVER['HTTP_HOST'] ?? ''));
