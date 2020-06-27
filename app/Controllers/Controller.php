<?php


namespace App\Controllers;


use mysqli;

class Controller
{
    protected mysqli $dbConnection;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $servername = "localhost";
        $username = "root";
        $password = "Nil00f@r1869";
        $dbName = "bibc";
        $this->dbConnection = new mysqli($servername, $username, $password, $dbName);
        if ($this->dbConnection->connect_error) {
            die("Connection failed: " . $this->dbConnection->connect_error);
        }
    }
}