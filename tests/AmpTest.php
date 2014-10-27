<?php
class test {
	public function doIt()
	{
		$arr = [1,2,3,4,5,6,7];
		var_dump(uasort($arr, ['test', 'sort']));
		var_dump($arr);
		
	}
	protected function sort($a, $b) {
		return 1;
	}
}

$t = new test;
$t->doIt();

$arr = [1,2,3,4,5,6,7];
		var_dump(uasort($arr, ['test', 'sort']));
var_dump($arr);
		
exit();

$my = new mysqli('127.0.0.1', 'lokkie', 'huihuf774', 'c9', 3306);
if ($my->connect_error) {
    die('Ошибка подключения (' . $my->connect_errno . ') '
            . $my->connect_error);
}


exit;


class AmpTest {
	
	protected static $_instance = array();
	
	protected $id = null;
	
	public function setID($id) 
	{
		$this->id = $id;
	}
	
	public function getId() 
	{
		return $this->id;
	}
	
	public static function &manage($id) {
		if (!isset(self::$_instance[$id])) {
			self::$_instance[$id] = new AmpTest;
			self::$_instance[$id]->setId($id);
		}
		return self::$_instance[$id];
	}
}


for ($i = 0; $i < 3; $i++) {
	var_dump($i);
	$cl = AmpTest::manage($i);
	var_dump($cl->getId());
}