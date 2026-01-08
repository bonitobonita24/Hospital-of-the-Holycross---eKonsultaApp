<?php
// Importer disabled: clean reset requested and completed.
http_response_code(410);
header('Content-Type: text/plain');
exit("Importer disabled. Please remove admin/import_konsulta.php from the project.\n");
