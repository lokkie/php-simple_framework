<?php
/**
 * User: A.Rusakevich
 * Date: 12.02.14
 * Time: 13:42
 */ 
 
 
 
 
# load path constants && autoloader
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "path_constants.php"; 
require_once implode(DIRECTORY_SEPARATOR , [dirname(__FILE__), 'core', 'autoLoad', 'AutoLoader.php']);
\core\autoLoad\AutoLoader::addCodeRoute('Basic');

