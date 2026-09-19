<?php
require_once __DIR__ . '/includes/db_config.php';
class DataProvider 
{
	public static function ExecuteQuery($sql)
	{
		$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME) or
			die ("couldn't connect to database");				
		$mysqli->query("set names 'utf8mb4'");		
		$result = $mysqli->query($sql);		
		$mysqli->close();		
		return $result;
	}
}
?>