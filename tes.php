<?php
// BISA
$server = '192.168.100.19';
$dbName = 'AXETADEV';
$uid = 'sa';
$pwd = 'albolabris';

// $conn = new PDO("sqlsrv:server=$server; database = $dbName", $uid, $pwd);
$conn = new PDO("sqlsrv:server=$server;TrustServerCertificate=true;ENCRYPT=true; database = $dbName", $uid, $pwd);
// $conn = new PDO("mysql:host={$server};dbname={$dbName}", $uid, $pwd);
$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );  

try {
    // $tableName = 'testTable';
    // $query = "CREATE TABLE $tableName ([c1_int] sql_variant, [c2_varchar] sql_variant)";
    
    // $stmt = $conn->query($query);
    // unset($stmt);
    
    // $query = "INSERT INTO [$tableName] (c1_int, c2_varchar) VALUES (1, 'test_data')";
    // $stmt = $conn->query($query);
    // unset($stmt);
    
    $tableName = 'BCCUSTOMSTABLE';
    $query = "SELECT TOP 10 * FROM $tableName";
    $stmt = $conn->query($query);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // echo '<pre>',print_r($result),'</pre>';
    echo json_encode($result);
    
    unset($stmt);
    unset($conn);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>