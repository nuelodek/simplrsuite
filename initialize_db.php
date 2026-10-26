<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
fwrite(STDERR, "Use php scripts/migrate.php --install with an empty database configured in your environment.\n");
exit(1);
