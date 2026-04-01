<?php
// Create .user.ini to disable opcache
$iniContent = <<<INI
opcache.enable=0
opcache.validate_timestamps=0
INI;

file_put_contents('/var/www/app.classapparelph.com/public/.user.ini', $iniContent);
echo ".user.ini created to disable opcache. Restarting Apache...<br>";

// Restart Apache
exec('sudo systemctl restart apache2 2>&1', $output, $returnCode);
echo "Apache restart result: " . ($returnCode === 0 ? 'SUCCESS' : 'FAILED') . "<br>";
echo "Output: " . implode("<br>", $output);
