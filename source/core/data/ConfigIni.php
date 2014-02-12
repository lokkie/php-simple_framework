<?php
namespace core\data;

/**
 * Класс-конфиг, позволяющий обрабатывать сложную конфигурацию основанную на ini файл.
 *    - при нахождении во входном ini файле зарезервированной секции <b>[include]</b> класс пытается включить файлы по маскам найденным в секции
 *    - класс позволяет обращаться к параметрам конфига как к свойствам объекта по маске "ИмяСекции::ИмяПараметра", что соотвествует ["имя-секции"]["имя-параметра"]<br />
 * <b>example.ini</b>
 * @code
 * [test-section]
 * test-param="test value"
 * test-param2="bla-bla"
 * @endcode
 * <b>example.php</b>
 * @code
 * $cfg = new ConfigIni('some_path/to_config.ini');
 * echo $cfg->{"TestSection::TestParam"};
 * # test value
 *
 * // Динамическое формирование адреса к переменной
 * $someSection = 'TestSection';
 * $someParam = 'TestParam2';
 * echo $cfg->{"{$someSection}::{$someParam}"};
 * # Bla-bla
 * @endcode
 *
 *  - в класс втроенна функции конвертации плейсхолдеров в строке
 * @code
 * $str = 'My name is {$name}. I\'m {$age} years old. I think {$name} is not bad name for {$profession}';
 * $dic = array(
 *        'name' => 'Alex',
 *        'age' => 27
 * );
 * echo ConfigIni::patternValue($str, $dict);
 * # My name is Alex. I'm 27 years old. I think Alex is not bad name for {$profession}
 *
 * // Удаляем неиспользованные плейсхолдеры
 * echo ConfigIni::patternValue($str, $dict, true);
 * # My name is Alex. I'm 27 years old. I think Alex is not bad name for
 * @endcode
 *
 * @author Lokkie (A.Rusakevich)
 */
class ConfigIni
{
    /**
     * Данные конфига
     * @var array $_configData
     */
    protected $_configData = array();
    /**
     * Список путей для автоподстановки
     * @var array $_path
     */
    protected $_path = array();

    /**
     * Для инициализации необходим входящий ini файл
     * @param string $config_file
     */
    public function __construct($config_file)
    {
        $conf_path = dirname($config_file);
        $this->_path['conf_path'] = $conf_path;
        $this->_path['project_dir'] = ROOT_PATH;
        $this->_configData = parse_ini_file($config_file, true);
        if (isset($this->_configData['include'])) {
            foreach ($this->_configData['include'] as $value) {
                $value = preg_replace('/\{\$conf_path\}/', $conf_path, $value);
                $list = glob($value);
                foreach ($list as $path) {
                    if (!is_dir($path) && file_exists($path)) {
                        $new_data = parse_ini_file($path, true);
                        foreach ($new_data as $key => $value) {
                            if (!isset($this->_configData[$key]))
                                $this->_configData[$key] = $value;
                        }
                    }
                }
            }
        }
    }

    /**
     * Parses string name of the param
     * @param string $name
     * @return array
     */
    protected function parseName($name)
    {
        return explode('::', strtolower(preg_replace('/(?<!^)([A-Z])/', '-\\1', $name)));
    }

    /**
     * "Магическая функция", позволяющая обращаться к элементам конфига как к свойствам класса
     * @param string $name
     */
    public function __get($name)
    {
        $name = $this->parseName($name);
        if (count($name) === 1)
            return @$this->_configData[$name[0]];
        else
            return @$this->_configData[$name[0]][ltrim($name[1], '-')];
    }

    /**
     * Magic method to check existing of config variable
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        $name = $this->parseName($name);
        if (count($name) === 1) {
            $result = @isset($this->_configData[$name[0]]);
        } else {
            $result = @isset($this->_configData[$name[0]][ltrim($name[1], '-')]);
        }
        return $result;
    }

    /**
     * Псевдоним "магической функции", позволяющей сделать предобработку путей в значении
     * @param string $name
     * @return string
     */
    public function getPath($name)
    {
        $path = $this->{$name};
        return self::patternValue($path, $this->_path);
    }

    /**
     * Позволяет использовать плейсхолдеры в строковых значениях и обрабатывая их подставлять значения.
     * @param string $pattern Входящая строка
     * @param array $values Ассоциированный массив замен
     * @param boolean $clear_empty Удалять ли плейсхолдеры, которым не нашлась замена
     * @return string
     */
    public static function patternValue($pattern, $values, $clear_empty = true)
    {
        preg_match_all('/\{\$(.*?)\}/', $pattern, $matches);
        $matches = $matches[1];
        $searches = array();
        $replaces = array();
        foreach ($matches as $key) {
            if (isset($values[$key])) {
                $searches[$key] = '/\{\$' . preg_quote($key) . '\}/';
                $replaces[$key] = $values[$key];
            } else if ($clear_empty) {
                $searches[$key] = '/\{\$' . preg_quote($key) . '\}/';
                $replaces[$key] = '';
            }
        }
        return preg_replace($searches, $replaces, $pattern);
    }
}

