<?php
# including auto-loading system 
require_once implode(DIRECTORY_SEPARATOR , [dirname(dirname(dirname(__FILE__))), 'core', 'autoLoad', 'AutoLoader.php']);
# enabling basic class route
\core\autoLoad\AutoLoader::addCodeRoute('Basic');
# enabling local auto-loading (through project)
\core\autoLoad\AutoLoader::addCodeRoute('Example', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes');
# load path constants
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "path_constants.php";

(new ExampleApplication)->run();