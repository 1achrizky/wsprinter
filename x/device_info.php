<?php
print_r($_SERVER['REMOTE_PORT']);
echo '<pre>',print_r($_SERVER),'</pre>';

echo '<h1>GET ENV</h1>';
echo '<pre>',print_r(getenv()),'</pre>';

?>