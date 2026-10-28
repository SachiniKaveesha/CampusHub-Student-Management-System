<?php

class Database
{
    public static $connection;

    public static function setUpConnection()
    {
        if (!isset(Database::$connection)) {
            try {
                Database::$connection = new mysqli("localhost", "root", "WLsk@2003@sachinikaveesha", "campushub", 3306);
            } catch (Exception $e) {
                Database::$connection = new mysqli("127.0.0.1", "root", "", "campushub", 3306);
            }

            if (Database::$connection->connect_error) {
                die("Database Connection Failed: " . Database::$connection->connect_error);
            }
        }
    }

    //Insert,Update,Delete
    public static function iud($q)
    {
        Database::setUpConnection();
        return Database::$connection->query($q);
    }

    //SELECT
    public static function search($q)
    {
        Database::setUpConnection();
        $resultset = Database::$connection->query($q);
        return $resultset;
    }
}
