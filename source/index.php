<?php
/**
 * User: A.Rusakevich
 * Date: 12.02.14
 * Time: 13:42
 */ 

echo "test";
 
# including auto-loading system 
require_once implode(DIRECTORY_SEPARATOR , [dirname(__FILE__), 'core', 'autoLoad', 'AutoLoader.php']);
# enabling basic class route
\core\autoLoad\AutoLoader::addCodeRoute('Basic');

# load path constants && autoloader
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "path_constants.php";

