<?php
function loader($class)
{
    $file = $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
}
spl_autoload_register('loader');

# including auto-loading system 
require_once implode(DIRECTORY_SEPARATOR , [dirname(dirname(__FILE__)), 'core', 'autoLoad', 'AutoLoader.php']);
# enabling basic class route
\core\autoLoad\AutoLoader::addCodeRoute('Basic');
