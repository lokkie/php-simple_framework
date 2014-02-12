<?php
namespace core\data\db;

/**
 * Надстройка над драйвером mysqli расширяющая его возможности:
 *     - добавленно использования плейсхолдеров DbDriver::query()
 *     - совместимость с балансировщиком нагрузки
 *     - поддержка внешнего отладчика
 *     - поддержка протоколирования ошибок
 * @author Lokkie (A.Rusakevich)
 */
class DbDriver extends \mysqli
{
    /**
     * Включен ли режим отладки
     * @var boolean $debugMode
     */
    public $debugMode = FALSE;
    /**
     * Сообщать об ошибках
     * @var boolean $errorReporting
     */
    public $errorReporting = TRUE;
    /**
     * Если указан файл лога, записывать ошибки в лог
     * @var boolean|string $logFile
     */
    public $logFile = FALSE;
    public $errorTracer;

    /**
     * Список Callback функция для отладки
     * @var array $_debug_callbacks
     */
    protected $_debug_callbacks = array();

    protected $_debug_callbacks_flush = '';

    /**
     * масив запросов базы данных
     * @var array $_query_data_container
     */
    protected $_query_data_container = array();
    /**
     * Сообщать об выполняемых запросах сразу - чтобы не ждать конецного вывода стека запросов
     * @var boolean $sqlShowAtOnce
     */
    public $sqlShowAtOnce = false;
    /**
     * Имя БД
     * @var string $_dbName
     */
    protected $_dbName = '';
    /**
     * Маркер вызова надстроек
     * @var boolean $_recall
     */
    protected $_recall = false;
    /**
     * Локальная ошибка
     * @var string $_local_error
     */
    protected $_local_error = '';
    /**
     * Код локальной ошибки
     * @var int $_local_errno
     */
    protected $_local_errno = -1;
    /**
     * Параметры передаваемые отладчику
     * @var array $_debug
     */
    protected $_debug = array();

    /**
     * Выбрать указанную БД
     * @param string $dbname
     * @return DbDriver
     */
    public function usedb($dbname)
    {
        $this->_dbName = $dbname;
        $this->select_db($dbname);
        return $this;
    }

    /**
     * <i>Формирует запрос к базе данных с использованием плейсхолдеров</i><br />
     * <b>Список плейсхолдеров:</b><br />
     * <b>?d</b> - данные число (целое или с плавающей точкой)<br />
     * <b>?#</b> - идентификаторы (имена полей, таблиц, БД, альясов). Примает строковые значения или массив (для списка идентификаторов)<br />
     * <b>?a</b> - массивыне данные (например для in()). Если подать ассоциированный массив разбирается по принципу ключ = значение<br />
     * <b>?</b>&nbsp; - данные, которые обрабатываются, как обычные строки<br /><br />
     * <b>?f</b>&nbsp; - данные не обрабатываются вообще (например для деректив ASC/DESC, функций и т.д.) <br /><br />
     * Все данные экранируются от SQL-иньекций<br />
     * <br />
     * <i><b>Примеры:</b></i><br /><br />
     * <b>Выборка данных:</b>
     * @code
     * // <code>
     *  $db->queryPs("SELECT * FROM ?# WHERE ?# = ? OR ?# in(?a) OR ?# > ?d", 'users', 'login', 'some_login', 'user_id', array(4,5,6,'3','f'), 'lastaction_time', 1552228.333);
     *  # На ввыходе в базу пойдет следующий запрос:
     *  # SELECT * FROM `users` WHERE `login` = "some_login" OR `user_id` in(4, 5, 6, "3", "f") OR `lastaction_time` > 1552228.333
     * // </code> @endcode
     * <b>2 примера удобной вставки данных:</b>
     * @code
     * // <code>
     * $data = array(
     *     'user_id' => 1,
     *     'nick' => 'ttt',
     *     'email' => 'test@gmail.com'
     * );
     * $db->queryPs('INSERT INTO ?# (?#) VALUES (?a)', 'users', array_keys($data), array_values($data));
     * $db->queryPs('INSERT INTO ?# SET ?a', 'users', $data);
     *
     * # На ввыходе в базу пойдут следующие запросы:<br />
     * # INSERT INTO `users` (`user_id`, `nick`, `email`) VALUES (1, "ttt", "test@gmail.com")<br />
     * # INSERT INTO `users` SET `user_id` = 1, `nick` = "ttt", `email` = "test@gmail.com"
     * // </code> @endcode
     * @param string $query
     * @param int $resultMode
     * @param mixed $resultMode ...
     * @return \mysqli_result
     *
     * @see DbDriver::selectCol()
     * @see DbDriver::selectCell()
     * @see DbDriver::selectIndexed()
     * @see DbDriver::selectRow()
     */
    public function query($query, $resultMode = MYSQLI_STORE_RESULT)
    {
        $this->_local_error = '';
        $this->_local_errno = -1;

        $debugInfo = array();
        $debugInfo['executionTime'] = 0;
        $debugInfo['inQuery'] = $query;
        $debugInfo['data'] = array();
        $debugInfo['args'] = func_get_args();
        $debugInfo['holders'] = array();
        $debugInfo['dbname'] = $this->_dbName;
        $phPattern = '/[= \r\n\t\(](\\?d|\\?#|\\?a|\\?f|\\?)[ ,=\r\n\t;\)]/';
        $phPattern1 = '/(\\?d|\\?#|\\?a|\\?f|\\?)/';
        $query .= ' ';
        if (preg_match_all($phPattern, $query, $preMatches)) {
            if (count($preMatches[0]) > (func_num_args() - 1)) {
                $this->_local_error = 'Not enough parametrs given. Given ' . func_num_args() . ' awaiting ' . (count($preMatches[0]) + 1);
                $this->_local_errno = 500;
                $debugInfo['error'] = true;
                $debugInfo['query'] = $query;
                $this->_reportDebug($debugInfo);
                return FALSE;
            }
            $replaces = array();
            foreach ($preMatches[1] as $key => $placeHolder) {
                $data = func_get_arg($key + 1);
                switch ($placeHolder) {
                    case '?d':
                        if (is_float($data)) {
                            $replaces[] = (float)$data;
                        } else {
                            $replaces[] = (int)$data;
                        }
                        break;
                    case '?':
                        $replaces[] = '"' . $this->escape_string($data) . '"';
                        break;
                    case '?#':
                        if (is_array($data)) {
                            $replaces[] = implode(',', array_map(array(&$this, 'escape_field'), $data));
                        } else {
                            $replaces[] = $this->escape_field($data);
                        }
                        break;
                    case '?a':
                        $replaces[] = implode(',', ($this->_is_assoc($data) ? $this->_escape_pairs($data) : array_map(array(&$this, 'escape_value'), $data)));
                        break;
                    case '?f':
                        $replaces[] = $data;
                        break;
                }
            }
            $phSearch = $hashReplacement = array_fill(0, count($preMatches[1]), $phPattern1);
            foreach ($hashReplacement as $key => $value) {
                $hashReplacement[$key] = sha1(microtime() . rand());
            }
            $debugInfo['data'] = $replaces;
            $debugInfo['holders'] = $preMatches[1];
            $query = str_replace($hashReplacement, $replaces, preg_replace($phSearch, $hashReplacement, $query, 1, $cnt));
            if (count($preMatches[0]) == (func_num_args() - 1)) {
                $resultMode = MYSQLI_STORE_RESULT;
            } else {
                $resultMode = func_get_arg(func_num_args() - 1);
            }
        }
        $qStarts = microtime(true);
        $res = parent::query($query, $resultMode);
        $debugInfo['executionTime'] = (microtime(true) - $qStarts) * 1000;
        $debugInfo['error'] = !$res;
        $debugInfo['query'] = $query;
        $this->_reportDebug($debugInfo);
        $this->_recall = FALSE;
        return $res;
    }

    /**
     * Псевдоним функции DbDriver::query()
     * @param string ...
     * @param mixed ...
     * @return \mysqli_result
     * @deprecated
     */
    public function queryPs()
    {
        $this->_recall = TRUE;
        return call_user_func_array(array($this, 'query'), func_get_args());
    }

    /**
     * Выбирает колонку из таблицы
     * Принимает параметры аналогичные методу DbDriver::query()<br />При ошибке БД возвращает FALSE
     * @param string ...
     * @param mixed ...
     * @return mixed
     *
     * @see DbDriver::query()
     * @see DbDriver::selectCell()
     * @see DbDriver::selectIndexed()
     * @see DbDriver::selectRow()
     */
    public function selectCol()
    {
        $this->_recall = TRUE;
        $res = call_user_func_array(array($this, 'query'), func_get_args());
        if (!$res) {
            return FALSE;
        }
        $col = array();
        while (NULL != ($row = $res->fetch_array())) {
            $col[] = $row[0];
        }
        return $col;
    }

    /**
     * <i>Выбирает ячейку из таблицы</i><br />
     * Принимает параметры аналогичные методу DbDriver::query()<br />При ошибке БД возвращает FALSE
     * @param string ...
     * @param mixed ...
     * @return mixed
     *
     * @see DbDriver::query()
     * @see DbDriver::selectCol()
     * @see DbDriver::selectIndexed()
     * @see DbDriver::selectRow()
     */
    public function selectCell()
    {
        $this->_recall = TRUE;
        $res = call_user_func_array(array($this, 'query'), func_get_args());
        if (!$res) {
            return FALSE;
        }
        $cell = NULL;
        if (NULL != ($row = $res->fetch_array())) {
            $cell = $row[0];
        }
        return $cell;
    }

    /**
     * <i>Выбирает из таблицы значение с индексацией по полю</i><br />
     * Первым параметром указываем поле, по которому <br />необходимо индексировать результат. Остальные параметры аналогичны DbDriver::query()<br />При ошибке БД возвращает FALSE
     * @param string $indexField
     * @param string ...
     * @param mixed ...
     * @return mixed
     *
     * @see DbDriver::query()
     * @see DbDriver::selectCol()
     * @see DbDriver::selectCell()
     * @see DbDriver::selectIndexed()
     * @see DbDriver::selectRow()
     */
    public function selectIndexed($indexField)
    {
        $this->_recall = TRUE;

        $args = array_slice(func_get_args(), 1);
        $res = call_user_func_array(array($this, 'query'), $args);
        $exclude = false;
        if (substr($indexField, 0, 1) == '!') {
            $exclude = true;
            $indexField = substr($indexField, 1);
        }
        if (!$res) {
            return FALSE;
        }

        $rows = array();
        while (NULL != ($row = $res->fetch_assoc())) {
            $index = $row[$indexField];
            if ($exclude) {
                unset($row[$indexField]);
            }
            $rows[$index] = $row;
        }
        return $rows;
    }

    /**
     * <i>Выбирает строку из таблицы</i><br />
     * Принимает параметры аналогичные методу DbDriver::query()<br />При ошибке БД возвращает FALSE
     *
     * @see DbDriver::query()
     * @return mixed
     *
     * @see DbDriver::query()
     * @see DbDriver::selectCol()
     * @see DbDriver::selectCell()
     * @see DbDriver::selectIndexed()
     * @see DbDriver::selectRow()
     */
    public function selectRow()
    {
        $this->_recall = TRUE;
        $args = func_get_args();
        $res = call_user_func_array(array($this, 'query'), $args);
        if (!$res) {
            return FALSE;
        }

        $ret_row = NULL;
        if (NULL != ($row = $res->fetch_assoc())) {
            $ret_row = $row;
        }

        return $ret_row;
    }

    /**
     * Добавляет отладчика
     * @param callable $listener
     **/
    public function addDebugListener($listener)
    {
        $this->_debug_callbacks[] = $listener;
    }

    /**
     * Создает отчет для отладки.
     * @param array $params В него попадает информация по запросу
     **/
    protected function _reportDebug($params)
    {
        $callStack = debug_backtrace(false);
        if ($this->_recall)
            $callId = 3;
        else
            $callId = 1;
        if (!isset($callStack[$callId]['file']))
            $callId++;
        $call = $callStack[$callId];


        // Елси мы получаем из инфо что в нем ошибка то репортим
        $callStack = NULL;
        if (@$params['error']) {
            $params['error_text'] = $this->error();
            $params['error_no'] = $this->errno();

            $error = $this->error() . ' (' . $this->errno() . ')';
            $time = @date('Y-m-d H:i:s');
            $error_text = <<<EOF
Database error:
    When: $time
    DataBase: {$this->_dbName}
    Query: <pre>{$params['query']} </pre>
    Error: $error
    Called in {$call['file']} on line {$call['line']}

EOF;
            if ($this->errorReporting) {
                if ($this->errorTracer != null && is_callable($this->errorTracer)) {
                    call_user_func($this->errorTracer, $error_text);
                } else {
                    echo $error_text;
                }
                //
            }
            if ($this->logFile) {
                @file_put_contents($this->logFile, $error_text, FILE_APPEND);
            }
        }

        // Елси подлючены дебагеры то заставляем их пройтись по инфе - !авось они накопают больше или у них своеобразный вывод
        // _debug_callbacks_flush - все что они выведут забрасываем вывод в него
        if ($this->debugMode) {
            $params['line'] = $call['line'];
            $params['file'] = $call['file'];

            $this->debugCallbacks($params);
            // $this->_query_data_container[] = $params;
        }
        return;
    }


    /**
     * Функция вызывает либо весь стек колбеков по очереди, либо конкретный колбэк
     * @param $params
     * @param bool|string $indexCallback
     */
    private function debugCallbacks($params, $indexCallback = false)
    {
        ob_start();
        if ($indexCallback === false) {
            if (count($this->_debug_callbacks) > 0) {
                foreach ($this->_debug_callbacks as $callback) {
                    call_user_func($callback, $params);
                }
            }
        } else {
            call_user_func($this->_debug_callbacks[$indexCallback], $params);
        }

        $echo = ob_get_contents();
        $this->_debug_callbacks_flush .= $echo;
        ob_end_clean();

        if ($this->sqlShowAtOnce == true)
            echo $echo;
    }

    /**
     * "Магическая функция", вызываемая перед уничтожением эксземпляра класса. Пытается понизить нагруженность ноды
     * @see DbFactory::freeConnection()
     * @see PoolBalancer::disconnectedFromServer()
     */
    public function __destruct()
    {
        if ($this->debugMode) {
            /*            if(count($this->_query_data_container)>0 and count($this->_debug_callbacks) > 0){
                            foreach ($this->_debug_callbacks as $callBack){
                                foreach ($this->_query_data_container as $queryData){
                                    $this->debugCallbacks($queryData,$callBack);
                                }
                            }
                        }

                        print_r($this->_debug_callbacks_flush); */
        }

        $this->close();
        DbFactory::freeConnection();
    }


    /* Misc methods */
    /**
     * Проверяет, являеся ли массив ассоциированным
     * @param array $array
     * @return boolean
     */
    protected function _is_assoc($array)
    {
        foreach (array_keys($array) as $k => $v) {
            if ($k !== $v)
                return true;
        }
        return false;
    }

    /**
     * Экранирует имя поля / таблицы / БД для безопасной вставки в запрос
     * @param mixed $field
     * @return mixed
     */
    public function escape_field($field)
    {
        return '`' . $this->escape_string($field) . '`';
    }

    /**
     * Экранирует ассоциативный массив, как пары поле-значение
     * @param array $data
     * @return array
     */
    protected function _escape_pairs($data)
    {
        $result = array();
        foreach ($data as $field => $value) {
            $result[] = '`' . $this->escape_string($field) . '` = ' . $this->escape_value($value);
            //$result[] = $pair;
        }
        return $result;
    }

    /**
     * Экранирует значение поля для безопасной вставки в запрос
     * @param mixed $value
     * @return mixed
     */
    public function escape_value($value)
    {
        if (is_null($value)) {
            $value = 'NULL';
        } elseif (is_int($value)) {
            $value = (int)$value;
        } elseif (is_float($value)) {
            $value = (float)$value;
        } elseif (is_bool($value)) {
            $value = (int)$value;
        } else {
            $value = '"' . $this->escape_string($value) . '"';
        }
        return $value;
    }

    /**
     * Геттер последней ошибки (исли установленна локальная ошибка - возвращается она, в противном случае ошибка БД - mysqli::error)
     * @return string
     */
    public function error()
    {
        return $this->_local_error != '' ? $this->_local_error : $this->error;
    }

    /**
     * Геттер кода последней ошибки (исли установленна локальная ошибка - возвращается она, в противном случае ошибка БД - mysqli::errno)
     * @return int
     */
    public function errno()
    {
        return $this->_local_errno != -1 ? $this->_local_errno : $this->errno;
    }


    /**
     * Возвращает данные отладки последнего действия
     * @return array
     */
    public function debug()
    {
        return $this->_debug;
    }

    public function getDebugFlush()
    {
        return $this->_debug_callbacks_flush;
    }
}