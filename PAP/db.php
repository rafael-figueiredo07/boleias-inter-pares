<?php

function conect_db()
{
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'rafael');
    define('DB_PASSWORD', '2007');
    define('DB_NAME', 'boleiasbd');

    try {
    	$link = new PDO(
			'mysql:host=' . DB_SERVER . 
			';dbname=' . DB_NAME . 
			';charset=utf8', 
			DB_USERNAME, DB_PASSWORD);
		$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $link;
    } catch (PDOException $e) {
    	exit('Falhou a conexão à base de dados!' . $e->getMessage());
		return $e->getMessage();
    }
}
?>