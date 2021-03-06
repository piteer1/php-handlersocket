--TEST--
string types
--SKIPIF--
--FILE--
<?php

require_once dirname(__FILE__) . '/../common/config.php';

$string_types = array(
    array('CHAR(10)', null, 1, 2, 5, 10),
    array('VARCHAR(10)', null, 1, 2, 5, 10),
    array('BINARY(10)', null, 1, 2, 5, 10),
    array('VARBINARY(10)', null, 1, 2, 5, 10),
    array('CHAR(255)', null, 1, 2, 5, 10, 100, 200, 255),
    array('VARCHAR(255)', null, 1, 2, 5, 10, 100, 200, 255),
    array('VARCHAR(511)', null, 1, 2, 5, 10, 100, 200, 511),
    array('LONGTEXT', 500, 1, 2, 5, 10, 100, 200, 511),
    array('LONGBLOB', 500, 1, 2, 5, 10, 100, 200, 511),
);

foreach ($string_types as $val)
{
    $type = array_shift($val);
    $length = array_shift($val);
    $vals = array();

    foreach ($val as $v)
    {
        $s = '';

        $arr = array();

        for ($i = 0; $i < $v; $i++)
        {
            $r = _rand($i);
            $arr[$i] = chr(65 + $r);
        }

        array_push($vals, implode('', $arr));
    }

    $vals = array_unique($vals);

    echo 'TYPE ', $type, PHP_EOL;

    test_one($type, $length, $vals);

    echo PHP_EOL;
}

function test_one($type, $length = null, $values = array())
{
    $length_str = '';
    if ($length !== null)
    {
        $length_str = '(' . $length . ')';
    }

    $mysql = get_mysql_connection();

    init_mysql_testdb($mysql);

    $table = 'hstesttbl';
    $sql = sprintf(
        'CREATE TABLE %s ( ' .
        'k ' . $type . ', ' .
        'v1 varchar(2047), ' .
        'v2 ' . $type . ', ' .
        'PRIMARY KEY(k' . $length_str . '), ' .
        'index i1(v1), index i2(v2' . $length_str . ', v1(300))) ' .
        'Engine = myisam default charset = latin1',
        mysql_real_escape_string($table));
    if (!mysql_query($sql, $mysql))
    {
        die(mysql_error());
    }

    $hs = new HandlerSocket(MYSQL_HOST, MYSQL_HANDLERSOCKET_PORT_WR);
    if (!($hs->openIndex(1, MYSQL_DBNAME, $table, '', 'k,v1,v2')))
    {
        die();
    }
    if (!($hs->openIndex(2, MYSQL_DBNAME, $table, 'i1', 'k,v1,v2')))
    {
        die();
    }
    if (!($hs->openIndex(3, MYSQL_DBNAME, $table, 'i2', 'k,v1,v2')))
    {
        die();
    }

    foreach ($values as $val)
    {
        $kstr = 's' . $val;

        $retval = $hs->executeSingle(1, '+', array($val, $kstr, $val), 0, 0);
        if ($retval === false)
        {
            echo $hs->getError(), PHP_EOL;
        }
    }

    foreach ($values as $val)
    {
        $kstr = 's' . $val;

        $retval = $hs->executeSingle(1, '=', array($val), 1, 0);
        if ($retval)
        {
            $retval = array_shift($retval);
            check_value($type . ':PK', $retval);
        }
        else
        {
            echo $hs->getError(), PHP_EOL;
        }

        $retval = $hs->executeSingle(2, '=', array($kstr), 1, 0);
        if ($retval)
        {
            $retval = array_shift($retval);
            check_value($type . ':I1', $retval);
        }
        else
        {
            echo $hs->getError(), PHP_EOL;
        }

        $retval = $hs->executeSingle(3, '=', array($val, $kstr), 1, 0);
        if ($retval)
        {
            $retval = array_shift($retval);
            check_value($type . ':I2', $retval);
        }
        else
        {
            echo $hs->getError(), PHP_EOL;
        }

        $retval = $hs->executeSingle(3, '=', array($val), 1, 0);
        if ($retval)
        {
            $retval = array_shift($retval);
            check_value($type . ':I2p', $retval);
        }
        else
        {
            echo $hs->getError(), PHP_EOL;
        }
    }

    mysql_close($mysql);
}

function check_value($msg, $vals)
{
    $k = '';
    $v1 = '';
    $v2 = '';

    if (isset($vals[0]))
    {
        $k = $vals[0];
    }
    if (isset($vals[1]))
    {
        $v1 = $vals[1];
    }
    if (isset($vals[2]))
    {
        $v2 = $vals[2];
    }

    if (strcmp($v2, $k) != 0)
    {
        echo $msg, ': V2 NE', PHP_EOL;
        echo $k, PHP_EOL;
        echo $v2, PHP_EOL;
        return;
    }
    if (strcmp($v1, 's' . $k) != 0)
    {
        echo $msg, ': V1 NE', PHP_EOL;
        echo $k, PHP_EOL;
        echo $v1, PHP_EOL;
        return;
    }
    echo $msg, ': EQ', PHP_EOL;
}

function _rand($i = 0)
{
    $rand = array(1, 6, 8, 9, 7, 5, 5, 4, 5, 3, 7, 7, 3, 4, 3, 1, 7, 7, 5, 4,
                  5, 9, 4, 8, 2, 8, 2, 4, 5, 5, 0, 3, 6, 6, 8, 4, 1, 2, 2, 6,
                  8, 3, 5, 9, 4, 2, 8, 9, 7, 1, 6, 9, 6, 9, 5, 2, 2, 1, 9, 4,
                  1, 1, 1, 2, 0, 6, 6, 2, 7, 5, 4, 0, 0, 8, 6, 3, 8, 6, 0, 0,
                  8, 1, 5, 8, 7, 7, 9, 1, 6, 4, 1, 6, 3, 0, 3, 1, 8, 8, 7, 7,
                  8, 9, 4, 3, 3, 3, 8, 1, 7, 8, 5, 3, 5, 8, 5, 0, 8, 5, 3, 8,
                  4, 8, 8, 3, 6, 4, 8, 6, 0, 7, 8, 2, 1, 8, 0, 2, 3, 5, 6, 9,
                  0, 9, 1, 0, 0, 4, 2, 4, 8, 6, 8, 3, 5, 5, 7, 5, 5, 5, 3, 3,
                  0, 3, 3, 8, 7, 6, 4, 2, 6, 4, 4, 0, 3, 3, 0, 7, 1, 0, 7, 6,
                  0, 7, 6, 8, 8, 0, 5, 5, 8, 7, 0, 6, 1, 3, 0, 7, 3, 2, 0, 7,
                  9, 7, 5, 6, 3, 9, 8, 4, 4, 1, 9, 3, 6, 3, 4, 5, 1, 7, 3, 6,
                  9, 6, 7, 4, 9, 3, 7, 1, 9, 3, 8, 1, 3, 0, 6, 8, 8, 1, 6, 0,
                  0, 7, 2, 9, 6, 5, 1, 2, 5, 4, 3, 1, 6, 3, 8, 1, 6, 0, 2, 1,
                  9, 5, 3, 5, 0, 3, 9, 4, 3, 5, 0, 2, 3, 2, 3, 9, 2, 8, 3, 4,
                  2, 4, 7, 9, 5, 1, 6, 2, 2, 1, 9, 0, 4, 2, 6, 4, 1, 2, 1, 5,
                  0, 6, 4, 8, 2, 1, 7, 0, 7, 8, 6, 6, 3, 1, 6, 4, 5, 4, 3, 7,
                  3, 8, 7, 8, 6, 5, 9, 8, 5, 2, 7, 6, 3, 2, 5, 2, 3, 0, 9, 8,
                  3, 3, 0, 0, 9, 2, 4, 8, 6, 3, 0, 5, 2, 6, 2, 4, 2, 8, 0, 3,
                  5, 5, 7, 9, 1, 4, 8, 1, 1, 8, 5, 8, 2, 6, 7, 8, 0, 5, 9, 2,
                  6, 9, 2, 7, 4, 1, 3, 8, 1, 5, 1, 0, 4, 3, 7, 5, 4, 3, 5, 9,
                  0, 6, 3, 6, 8, 1, 7, 5, 9, 8, 4, 5, 7, 6, 9, 7, 2, 4, 3, 0,
                  8, 1, 9, 3, 7, 8, 5, 3, 4, 3, 2, 5, 3, 8, 8, 5, 0, 4, 4, 6,
                  0, 1, 3, 8, 1, 7, 2, 6, 2, 9, 3, 2, 7, 9, 9, 8, 2, 4, 5, 8,
                  9, 7, 3, 1, 6, 2, 6, 9, 7, 0, 9, 1, 7, 5, 7, 8, 7, 2, 2, 7,
                  3, 9, 9, 1, 9, 5, 3, 7, 2, 8, 0, 5, 0, 9, 7, 1, 0, 4, 2, 0,
                  8, 1, 7, 6, 7, 8, 5, 2, 3, 0, 6);
    return $rand[$i];
}

--EXPECT--
TYPE CHAR(10)
CHAR(10):PK: EQ
CHAR(10):I1: EQ
CHAR(10):I2: EQ
CHAR(10):I2p: EQ
CHAR(10):PK: EQ
CHAR(10):I1: EQ
CHAR(10):I2: EQ
CHAR(10):I2p: EQ
CHAR(10):PK: EQ
CHAR(10):I1: EQ
CHAR(10):I2: EQ
CHAR(10):I2p: EQ
CHAR(10):PK: EQ
CHAR(10):I1: EQ
CHAR(10):I2: EQ
CHAR(10):I2p: EQ

TYPE VARCHAR(10)
VARCHAR(10):PK: EQ
VARCHAR(10):I1: EQ
VARCHAR(10):I2: EQ
VARCHAR(10):I2p: EQ
VARCHAR(10):PK: EQ
VARCHAR(10):I1: EQ
VARCHAR(10):I2: EQ
VARCHAR(10):I2p: EQ
VARCHAR(10):PK: EQ
VARCHAR(10):I1: EQ
VARCHAR(10):I2: EQ
VARCHAR(10):I2p: EQ
VARCHAR(10):PK: EQ
VARCHAR(10):I1: EQ
VARCHAR(10):I2: EQ
VARCHAR(10):I2p: EQ

TYPE BINARY(10)
BINARY(10):PK: V1 NE
B         
sB
BINARY(10):I1: V1 NE
B         
sB
BINARY(10):I2: V1 NE
B         
sB
BINARY(10):I2p: V1 NE
B         
sB
BINARY(10):PK: V1 NE
BG        
sBG
BINARY(10):I1: V1 NE
BG        
sBG
BINARY(10):I2: V1 NE
BG        
sBG
BINARY(10):I2p: V1 NE
BG        
sBG
BINARY(10):PK: V1 NE
BGIJH     
sBGIJH
BINARY(10):I1: V1 NE
BGIJH     
sBGIJH
BINARY(10):I2: V1 NE
BGIJH     
sBGIJH
BINARY(10):I2p: V1 NE
BGIJH     
sBGIJH
BINARY(10):PK: EQ
BINARY(10):I1: EQ
BINARY(10):I2: EQ
BINARY(10):I2p: EQ

TYPE VARBINARY(10)
VARBINARY(10):PK: EQ
VARBINARY(10):I1: EQ
VARBINARY(10):I2: EQ
VARBINARY(10):I2p: EQ
VARBINARY(10):PK: EQ
VARBINARY(10):I1: EQ
VARBINARY(10):I2: EQ
VARBINARY(10):I2p: EQ
VARBINARY(10):PK: EQ
VARBINARY(10):I1: EQ
VARBINARY(10):I2: EQ
VARBINARY(10):I2p: EQ
VARBINARY(10):PK: EQ
VARBINARY(10):I1: EQ
VARBINARY(10):I2: EQ
VARBINARY(10):I2p: EQ

TYPE CHAR(255)
CHAR(255):PK: EQ
CHAR(255):I1: EQ
CHAR(255):I2: EQ
CHAR(255):I2p: EQ
CHAR(255):PK: EQ
CHAR(255):I1: EQ
CHAR(255):I2: EQ
CHAR(255):I2p: EQ
CHAR(255):PK: EQ
CHAR(255):I1: EQ
CHAR(255):I2: EQ
CHAR(255):I2p: EQ
CHAR(255):PK: EQ
CHAR(255):I1: EQ
CHAR(255):I2: EQ
CHAR(255):I2p: EQ
CHAR(255):PK: EQ
CHAR(255):I1: EQ
CHAR(255):I2: EQ
CHAR(255):I2p: EQ
CHAR(255):PK: EQ
CHAR(255):I1: EQ
CHAR(255):I2: EQ
CHAR(255):I2p: EQ
CHAR(255):PK: EQ
CHAR(255):I1: EQ
CHAR(255):I2: EQ
CHAR(255):I2p: EQ

TYPE VARCHAR(255)
VARCHAR(255):PK: EQ
VARCHAR(255):I1: EQ
VARCHAR(255):I2: EQ
VARCHAR(255):I2p: EQ
VARCHAR(255):PK: EQ
VARCHAR(255):I1: EQ
VARCHAR(255):I2: EQ
VARCHAR(255):I2p: EQ
VARCHAR(255):PK: EQ
VARCHAR(255):I1: EQ
VARCHAR(255):I2: EQ
VARCHAR(255):I2p: EQ
VARCHAR(255):PK: EQ
VARCHAR(255):I1: EQ
VARCHAR(255):I2: EQ
VARCHAR(255):I2p: EQ
VARCHAR(255):PK: EQ
VARCHAR(255):I1: EQ
VARCHAR(255):I2: EQ
VARCHAR(255):I2p: EQ
VARCHAR(255):PK: EQ
VARCHAR(255):I1: EQ
VARCHAR(255):I2: EQ
VARCHAR(255):I2p: EQ
VARCHAR(255):PK: EQ
VARCHAR(255):I1: EQ
VARCHAR(255):I2: EQ
VARCHAR(255):I2p: EQ

TYPE VARCHAR(511)
VARCHAR(511):PK: EQ
VARCHAR(511):I1: EQ
VARCHAR(511):I2: EQ
VARCHAR(511):I2p: EQ
VARCHAR(511):PK: EQ
VARCHAR(511):I1: EQ
VARCHAR(511):I2: EQ
VARCHAR(511):I2p: EQ
VARCHAR(511):PK: EQ
VARCHAR(511):I1: EQ
VARCHAR(511):I2: EQ
VARCHAR(511):I2p: EQ
VARCHAR(511):PK: EQ
VARCHAR(511):I1: EQ
VARCHAR(511):I2: EQ
VARCHAR(511):I2p: EQ
VARCHAR(511):PK: EQ
VARCHAR(511):I1: EQ
VARCHAR(511):I2: EQ
VARCHAR(511):I2p: EQ
VARCHAR(511):PK: EQ
VARCHAR(511):I1: EQ
VARCHAR(511):I2: EQ
VARCHAR(511):I2p: EQ
VARCHAR(511):PK: EQ
VARCHAR(511):I1: EQ
VARCHAR(511):I2: EQ
VARCHAR(511):I2p: EQ

TYPE LONGTEXT
LONGTEXT:PK: EQ
LONGTEXT:I1: EQ
LONGTEXT:I2: EQ
LONGTEXT:I2p: EQ
LONGTEXT:PK: EQ
LONGTEXT:I1: EQ
LONGTEXT:I2: EQ
LONGTEXT:I2p: EQ
LONGTEXT:PK: EQ
LONGTEXT:I1: EQ
LONGTEXT:I2: EQ
LONGTEXT:I2p: EQ
LONGTEXT:PK: EQ
LONGTEXT:I1: EQ
LONGTEXT:I2: EQ
LONGTEXT:I2p: EQ
LONGTEXT:PK: EQ
LONGTEXT:I1: EQ
LONGTEXT:I2: EQ
LONGTEXT:I2p: EQ
LONGTEXT:PK: EQ
LONGTEXT:I1: EQ
LONGTEXT:I2: EQ
LONGTEXT:I2p: EQ
LONGTEXT:PK: EQ
LONGTEXT:I1: EQ
LONGTEXT:I2: EQ
LONGTEXT:I2p: EQ

TYPE LONGBLOB
LONGBLOB:PK: EQ
LONGBLOB:I1: EQ
LONGBLOB:I2: EQ
LONGBLOB:I2p: EQ
LONGBLOB:PK: EQ
LONGBLOB:I1: EQ
LONGBLOB:I2: EQ
LONGBLOB:I2p: EQ
LONGBLOB:PK: EQ
LONGBLOB:I1: EQ
LONGBLOB:I2: EQ
LONGBLOB:I2p: EQ
LONGBLOB:PK: EQ
LONGBLOB:I1: EQ
LONGBLOB:I2: EQ
LONGBLOB:I2p: EQ
LONGBLOB:PK: EQ
LONGBLOB:I1: EQ
LONGBLOB:I2: EQ
LONGBLOB:I2p: EQ
LONGBLOB:PK: EQ
LONGBLOB:I1: EQ
LONGBLOB:I2: EQ
LONGBLOB:I2p: EQ
LONGBLOB:PK: EQ
LONGBLOB:I1: EQ
LONGBLOB:I2: EQ
LONGBLOB:I2p: EQ

