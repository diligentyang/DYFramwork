<?php
namespace lib\Database;
use \systems\DYBaseFunc as DYBaseFunc;

class Pdo implements IDataBase
{
    private static $_instance = null;
    private $db;

    private function __construct($dbname, $host, $db_user, $db_pwd, $charset)
    {
        $dsn = "mysql:host=$host;dbname=$dbname";
        try {
            $this->db = new \PDO($dsn, $db_user, $db_pwd);
        } catch (\PDOException $e) {
            echo '数据库连接失败' . $e->getMessage();
            exit();
        }
        $this->db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->db->exec("set names $charset");
    }

    public static function getInstance($dbname, $host, $db_user, $db_pwd, $charset)
    {
        if(!self::$_instance){
            self::$_instance = new self($dbname, $host, $db_user, $db_pwd, $charset);
        }
        return self::$_instance;
    }

    /**
     * 执行SQL查询语句，或简单执行其他语句
     *
     * @param $strSql sql语句
     * @param string $mode 返回类型
     * @return array|int|\PDOStatement
     */
    function query($strSql, $mode = "array")
    {
        $strSql = trim($strSql);
        if($strSql==""){
            DYBaseFunc::showErrors("Query cannot be empty!");
        }
        $query = $this->db->query($strSql);
        if(!$query){
            DYBaseFunc::showErrors("DataBase error : ".$this->getPDOError());
        }
        if (strpos(strtolower($strSql), "select") ===false) {
            return $query;
        }
        switch ($mode) {
            case 'array' :
                $res = $query->fetchAll(\PDO::FETCH_ASSOC);
                break;
            case 'object' :
                $res = $query->fetchAll(\PDO::FETCH_OBJ);
                break;
            case 'count':
                $res = $query->rowCount();
                break;
            default:
                DYBaseFunc::showErrors("SQLERROR: please check your second param!");
        }
        return $res;
    }

    function insert($table, $arrayDataValue)
    {
        $this->checkFields($table, array_keys($arrayDataValue));
        
    }

    private function checkFields($table, $array)
    {
        $fields = $this->getColumns($table);
        foreach($array as $key){
            if(!in_array($key, $fields)){
                DYBaseFunc::showErrors("SQLERROR : Unknown column `$key` in field list.");
            }
        }
    }

    function getColumns($table)
    {
        $fields = array();
        $recordset = $this->db->query("SHOW COLUMNS FROM $table");
        $this->getPDOError();
        $recordset->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $recordset->fetchAll();
        foreach ($result as $rows) {
            $fields[] = $rows['Field'];
        }
        return $fields;
    }

    /**
     * 获取PDO执行的错误
     *
     * @return mixed
     */
    private function getPDOError()
    {
        if ($this->db->errorCode() != '00000') {
            $arrayError = $this->db->errorInfo();
            return $arrayError[2];
        }
    }

}