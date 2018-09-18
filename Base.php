<?php

/**
 * @author Oleg Isaev
 * @contacts vk.com/id50416641, t.me/pandcar, github.com/pandcar
 * @version 2.1.8
 */

class Base
{
    public static $pdo = false,		// Объект PDO
				  $log = [],		// Лог
				  $debug = false;	// Включение более полной отчётности в лог
	
	protected static $transaction = false;
	
	// Начало транзакции
	public static function transaction()
	{
		if (self::$pdo && !self::$transaction)
		{
			try {
				$bool = self::$pdo->beginTransaction();
				self::$transaction = 0;
				
				self::$log[] = 'Transaction included'.(self::$debug ? "\n".(new \Exception)->getTraceAsString() : null);
				
				return $bool;
			}
			catch (\PDOException $e) {
				self::$log[] = 'Transaction error'."\n".$e->getMessage().(self::$debug ? "\n".(new \Exception)->getTraceAsString() : null);
				
				return false;
			}
		}
		else
		{
			self::$log[] = 'Transaction'."\n".'No connection to the database or the transaction is already running'.(self::$debug ? "\n".(new \Exception)->getTraceAsString() : null);
		}
		
		return false;
	}
	
	// Конец транзакции c проверкой
	public static function commit()
	{
		if (self::$pdo && is_int(self::$transaction))
		{
			$count = self::$transaction;
			self::$transaction = false;
			
			if ($count == 0)
			{
				self::$pdo->commit();
				
				self::$log[] = 'Commit successfully'.(self::$debug ? "\n".(new \Exception)->getTraceAsString() : null);
				
				return true;
			}
			else
			{
				self::$pdo->rollBack();
				
				self::$log[] = 'Commit rollback'.(self::$debug ? "\n".(new \Exception)->getTraceAsString() : null);
			}
		}
		else
		{
			self::$log[] = 'Commit'."\n".'No connection to the database or transaction is not running'.(self::$debug ? "\n".(new \Exception)->getTraceAsString() : null);
		}
		
		return false;
	}
	
	// Получить строку
	public static function get($table, $mixed_var = [], $param = [], $select = '*')
	{
		list($part_sql, $param) = self::genPartSql($mixed_var, $param, ' AND ');
		
		return self::query('SELECT '.$select.' FROM `'.self::safely($table).'` WHERE '.$part_sql.' LIMIT 1', $param);
	}
	
	// Количество строк int
	public static function count($table, $mixed_var = [], $param = [])
	{
		return self::get($table, $mixed_var, $param, 'COUNT(`id`)');
	}
	
	// Удаляет строку
	public static function remove($table, $mixed_var = [], $param = [])
	{
		list($part_sql, $param) = self::genPartSql($mixed_var, $param, ' AND ');
		
		return self::query('DELETE FROM `'.self::safely($table).'` WHERE '.$part_sql, $param);
	}
	
	// Обновляет строку
	public static function update($table, $mixed_var = [], $param = [])
	{
		list($sel_sql, $sel_params) = self::genPartSql($mixed_var, [], ' AND ', 'sel_');
		list($up_sql, $up_params) = self::genPartSql($param, [], ', ', 'up_');
		
		return self::query('UPDATE `'.self::safely($table).'` SET '.$up_sql.' WHERE '.$sel_sql, array_merge($sel_params, $up_params));
	}
	
	// Добавляет строку
	public static function add($table, $param = [])
	{
		$str_column = '';
		$str_values = '';
		$array = [];
		
		foreach ($param as $column => $value)
		{
			$name_column = explode('/', $column)[0];
			
			$str_column .= (! empty($str_column) ? ', ' : null).'`'.$name_column.'`';
			$str_values .= (! empty($str_values) ? ', ' : null).':'.$name_column;
			
			$array[$column] = $value;
		}
		
		return self::query('INSERT INTO `'.self::safely($table).'` ('.$str_column.') VALUES ('.$str_values.')', $array);
	}
	
	// SQL запрос
	public static function query($str_sql, $param = [], $all_type = false)
	{
		if (self::$pdo)
		{
			try {
				if (self::$debug) {
					$time = microtime(true);
				}
				
				if (! empty($param))
				{
					$stm = self::$pdo->prepare($str_sql);
					
					foreach ($param as $key => $value)
					{
						$exp = explode('/', $key);
						
						$value = (! empty($value) ? $value : '');
						$set_type = \PDO::PARAM_STR;
						
						if (isset($exp[1]) && $exp[1] == 'int')
						{
							$value = (int) $value;
							$set_type = \PDO::PARAM_INT;
						}
						
						$stm->bindValue(':'.$exp[0], $value, $set_type);
					}
					
					$stm->execute();
				}
				else
				{
					$stm = self::$pdo->query($str_sql);
				}
				
				if (self::$debug) {
					self::$log[] = 'Query time: '.number_format( microtime(true) - $time, 5).' sec'."\n".(new \Exception)->getTraceAsString();
				}
			}
			catch (\PDOException $e) {
				if (is_int(self::$transaction)) {
					self::$transaction++;
				}
				
				self::$log[] = 'Query error'."\n".$e->getMessage().(self::$debug ? "\n".(new \Exception)->getTraceAsString() : null);
				
				return false;
			}
			
			if ($stm)
			{
				preg_match('~^(/\*.+?\*/ *|)([^ ]+)~i', trim($str_sql), $preg);
				$comand = mb_strtolower($preg[2], 'UTF-8');
				
				if ($comand == 'select')
				{
					if ($all_type == 'arr')
					{
						return $stm->fetchAll(\PDO::FETCH_ASSOC);
					}
					elseif ($all_type == 'gen')
					{
						return self::queryGenerator($stm);
					}
					elseif ($array = $stm->fetch(\PDO::FETCH_ASSOC))
					{
						if (count($array) == 1)
						{
							if (substr_count( array_keys($array)[0], '(' ) > 0)
							{
								return array_values($array)[0];
							}
						}
						
						return $array;
					}
					
					return false;
				}
				elseif ($comand == 'insert')
				{
					return self::$pdo->lastInsertId();
				}
				
				return true;
			}
		}
		
		return false;
	}
	
	// Подключение к базе данных
	public static function connect($type = 'mysql', $param = [], $opt = [])
	{
		if (! empty($opt['debug']))
		{
			self::debug();
		}
		
		switch ($type)
		{
			case 'mysql':
				$dsn = 'mysql:host='.(! empty($param['host']) ? $param['host'] : 'localhost').';'.(! empty($param['port']) ? 'port='.$param['port'].';' : null).'dbname='.$param['base'].';charset='.(! empty($param['charset']) ? $param['charset'] : 'utf8');
				break;
			case 'postgresql':
				$dsn = 'pgsql:host='.(! empty($param['host']) ? $param['host'] : 'localhost').';dbname='.$param['base'].';options="--client_encoding='.(! empty($param['charset']) ? $param['charset'] : 'utf8').'"';
				break;
			case 'oracle':
				$dsn = 'oci:dbname='.(! empty($param['host']) ? $param['host'] : 'localhost').'/'.$param['base'].';charset='.(! empty($param['charset']) ? $param['charset'] : 'utf8');
				break;
			case 'sqlite':
				$dsn = 'sqlite:'.$param['path'];
				break;
		}
		
		if (isset($dsn))
		{
			try {
				self::$pdo = new \PDO($dsn, (! empty($param['user']) ? $param['user'] : 'root'), (! empty($param['pass']) ? $param['pass'] : ''), [
					\PDO::ATTR_ERRMODE				=> \PDO::ERRMODE_EXCEPTION, 
					\PDO::ATTR_DEFAULT_FETCH_MODE	=> \PDO::FETCH_ASSOC,
				]);
				
				self::$log[] = 'Successfully connected to the '.$type.' database'.(self::$debug ? "\n".(new \Exception)->getTraceAsString() : null);
			}
			catch (\PDOException $e) {
				self::$log[] = 'Connect '.$type.' error'."\n".$e->getMessage().(self::$debug ? "\n".(new \Exception)->getTraceAsString() : null);
				
				return false;
			}
			
			return self::$pdo;
		}
		
		return false;
	}
	
	// Экранирования строки слэшами для использования в запросах (не самый надёжный вариант)
	public static function safely($string)
	{
		return addslashes($string);
	}
	
	// Debug
	public static function debug()
	{
		self::$debug = true;
		
		register_shutdown_function(function(){
			echo "<br>\n<br>\n<pre style='margin:2px;padding:7px;background-color: #FFF7B5;'>\n";
			echo implode("\n------------\n", self::$log);
			echo "\n</pre>";
		});
	}
	
	// Удаляем объект PDO
	public static function end()
	{
		self::$log[] = 'End'."\n".(new \Exception)->getTraceAsString();
		
		self::$pdo = null;
	}
	
	protected static function queryGenerator($stm)
	{
		while ($row = $stm->fetch(\PDO::FETCH_ASSOC))
		{
			yield $row;
		}
	}
	
	protected static function genPartSql($mixed_var, $param = [], $glue = ', ', $prefix = '')
	{
		if (is_int($mixed_var))
		{
			$part_sql = '`id` = :id';
			$param = [
				'id' => $mixed_var
			];
		}
		elseif (is_array($mixed_var))
		{
			$part_sql = [];
			$param = [];
			
			foreach ($mixed_var as $column => $value)
			{
				$name_column = explode('/', $column)[0];
				
				$part_sql[] = '`'.$name_column.'` = :'. $prefix . $name_column;
				
				$param[ $prefix . $column ] = $value;
			}
			
			$part_sql = implode($glue, $part_sql);
		}
		else
		{
			$part_sql = $mixed_var;
		}
		
		return [
			$part_sql, 
			$param
		];
	}
}
