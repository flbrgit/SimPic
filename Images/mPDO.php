<?php
/**
 * Class mPDO
 *
 * This class extends the main PDO class by providing just one additional method
 * in order to prepare for adding multiple records at a time
 *
 * @param string $dsn as for PDO, e.g. 'mysql:host=localhost;dbname=mydb'
 * @param string optional $username as for PDO, e.g. 'root'
 * @param string optional $password as for PDO
 * @param array optional $options as for PDO
 *
 * @return mPDO object on success
 */

class mPDO extends PDO
{
   public function __construct($dsn, $username, $password, $options=null)
    {
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('mPDOStatement', array($this)));
    }

    public function multiPrepare($sql, $data)
    {
        $rows = count($data);
        $cols = count($data[0]);
        $rowString = '(' . rtrim(str_repeat('?,', $cols), ',') . '),';
        $valString = rtrim(str_repeat($rowString, $rows), ',');
        return $this->prepare($sql . ' VALUES ' . $valString);
    }
}

/**
 * Class mPDOStatement
 *
 * This class extends the main PDOStatement class by providing just one additional method
 * in order to bind multiple records to a prepared statement in a single execution
 *
 * @param mPDO (PDO) object $dbh
 *
 * @return mPDOStatement object on success
 */

class mPDOStatement extends PDOStatement
{
    public $dbh;

    protected function __construct($dbh) {
        $this->dbh = $dbh;
    }

    public function multiExecute($data)
    {
        $bindArray = array();
        array_walk_recursive($data, function($item) use (&$bindArray) { $bindArray[] = $item; });
        $this->execute($bindArray);
    }
}