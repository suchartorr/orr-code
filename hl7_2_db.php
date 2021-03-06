<?php

/*
 * The MIT License
 *
 * Copyright 2559 it.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
require_once './orr_lib/hl7.php';

/**
 * Description of hl7_2_db
 *
 * @author suchart bunhachirat
 */
class hl7_2_db {

    private $hl7;
    private $order_file = array();
    private $conn = null;

    public function __construct() {
        
        $path_filename = "./ext/lis/res/151008206007219.hl7"; //ชื่อภาษาไทย ต้องแปลงเป็น UTF8
        //$path_filename = "./ext/lis/res/151010206004213.hl7"; //Lab เยอะ
        try {
            $this->hl7 = new HL7($path_filename);
            echo $this->insert_order();
            $this->test_order();
            print_r($this->hl7->segment_count);
        } catch (Exception $ex) {
            echo 'Caught exception: ', $ex->getMessage(), "\n";
        }
    }

    public function test_order() {
        $message = $this->hl7->get_message();
        $this->order_file['message_date'] = $message[0]->fields[5];
        $this->order_file['patient_id'] = $message[1]->fields[2];
        //$this->record['order_number'] = $message[4]->fields[1];
        $this->order_file['transection_date'] = $message[3]->fields[8];
        $this->order_file['order_comment'] = $message[5]->fields[8];

        print_r($this->order_file);
        print_r($this->hl7->get_message());
    }

    protected function insert_order() {
        $dsn = 'mysql:host=localhost;dbname=orr-code';
        $username = 'orr-code';
        $password = '';
        $options = array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        );

        try {
            $this->conn = new PDO($dsn, $username, $password, $options);
        } catch (Exception $ex) {
            echo "Could not connect to database : " . $ex->getMessage();
            exit();
        }
        $message = $this->hl7->get_message();
        //$stmt->execute(array(":message_date" => $message[0]->fields[5], ":patient_id" => $message[1]->fields[2]));
        $sql = "INSERT INTO MyGuests (firstname, lastname, email)
    VALUES (:firstname, :lastname, :email)";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(array(':firstname' => $message[1]->fields[4], ':lastname' => 'LIS Testing', ':email' => $message[1]->fields[2]));
            echo 'New record id : ' . $this->conn->lastInsertId();
        } catch (Exception $ex) {
            echo "Could not create table : " . $ex->getMessage();
        }
    }

}

$my = new hl7_2_db();
