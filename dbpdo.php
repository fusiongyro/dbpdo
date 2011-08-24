<?
/**
 * PDO to DB Facade
 *
 * If you loved PEAR DB but can't stand the whining to start using PDO, you've come to the right place
 * 
 * PHP version 5
 *
 * Copyright (c) 2009 Daniel Lyons
 * 
 * @category  Compatibility
 * @package   PDO_to_DB_Facade
 * @author    Daniel Lyons <fusion@storytotell.org>
 * @copyright 2009 Daniel Lyons
 * @license   BSD-Revised
 */

// turn this off if you don't want the six global functions at the bottom
// which use the $DB variable as a DBPDO object.
define('I_WANT_GLOBAL_DB', true);

// In OO terms, this class is a (slightly enhanced) adapter from PDO to the
// old Pear DB. Pear DB was shockingly convenient and I got so used to it that
// the thought of using PDO's clunky API sickens me. This is much easier.
class PDO2DB
{
  var $DB;
  
  function __construct(&$PDO)
  {
    $this->DB =& $PDO;
  }
  
  // connect to the database, using PDO
  function query($statement, $args=array())
  {
  	// in case we get a scalar value
  	if (!is_array($args))
  		$args = array($args);

  	if (count($args) == 0)
  	{
  		// no arguments; just execute and return the result object
  		return $this->DB->query($statement);
  	}
  	else
  	{
  		// prepare the statement and then execute it against $args, returning the result
  		$sth = $this->DB->prepare($statement);
  		$sth->execute($args);
  		return $sth;
  	}
  }

  // a bunch of compatibility/convenience functions for old PEAR DB

  // get all the rows of the result set
  function getAll($sql, $args=array())
  {
  	$sth = $this->query($sql, $args);
  	return $sth->fetchAll(PDO::FETCH_NAMED);	
  }

  // get all the values in a column of the result set
  function getCol($sql, $args=array(), $col=0) 
  { 
  	$sth = $this->query($sql, $args);
  	return $sth->fetchAll(PDO::FETCH_COLUMN, $col);
  }

  // this function takes a resultset of rows of two columns and returns a single
  // hash of col1 => col2
  function getAssoc($sql, $args=array()) { 
  	$sth = $this->query($sql, $args);
  	return $sth->fetchAll(PDO::FETCH_KEY_PAIR);
  }

  // get a single value from the first row of the result set
  function getOne($sql, $args=array(), $col=0)
  {
  	$sth = $this->query($sql, $args);
  	$res = $sth->fetch(PDO::FETCH_NUM);
  	return $res[$col];
  }

  // get the first row of the result set
  function getRow($sql, $args=array())
  {
  	$sth = $this->query($sql, $args);
  	return $sth->fetch(PDO::FETCH_NAMED);
  }
  
  function beginTransaction()
  {
  	$this->DB->beginTransaction();
  }
  
  function commit()
  {
  	$this->DB->commit();
  }
  
  function rollback()
  {
  	$this->DB->rollback();
  }
}

// these functions are pass-throughs to some global instance called DB of a
// DBPDO wrapper. You don't have to have them if you don't want them, but I
// find it very convenient.
if (I_WANT_GLOBAL_DB)
{
  function query($statement, $args=array())
  { return $GLOBALS['DB']->query($statement, $args);   }

  function getAll($sql, $args=array())
  { return $GLOBALS['DB']->getAll($sql, $args);   }

  function getCol($sql, $args=array(), $col=0) 
  { return $GLOBALS['DB']->getCol($sql, $args, $col);  }

  function getAssoc($sql, $args=array()) 
  { return $GLOBALS['DB']->getAssoc($sql, $args); }

  function getOne($sql, $args=array(), $col=0)
  { return $GLOBALS['DB']->getOne($sql, $args, $col); }

  function getRow($sql, $args=array())
  {  return $GLOBALS['DB']->getRow($sql, $args);  }
  
  function beginTransaction()
  {  return $GLOBALS['DB']->beginTransaction(); }

  function commit()
  {  return $GLOBALS['DB']->commit(); }

  function rollback()
  {  return $GLOBALS['DB']->rollback(); }
}

// a handy exception-ish handling method. use for your own error handling.
function check($res, $message=NULL)
{
	// if we do not have an error, we return it immediately.
	if (!PEAR::isError($res))
		return $res;
		
	// otherwise, if we have a message, we show that; if not, we show the 
	// database's error message instead.
	else if ($message): ?>
		<p><?= $message ?></p>
	<? else: ?>
		<p><b>Database error:</b> <?= $res->getMessage() ?></p>
		<p>Details:
			<pre><code><?= $res->getUserInfo(); ?></code></pre>
		</p>
	<? endif;
	
	// since we didn't return earlier when we realized it wasn't an error, we die now.
	die;
}

?>
