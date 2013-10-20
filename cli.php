<?php
echo "Loading framework...\n";
include dirname(__FILE__) . "/phpcli.inc";

echo "============================\n";
echo "PhpCli " . App::$VERSION . "\n";
echo "   script:do-a : do something neat\n";
echo "   script:do-b : do something neat\n";

exit (0);