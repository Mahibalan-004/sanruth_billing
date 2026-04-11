<?php
require_once("config/db.php");

$tables = [];
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_array($result)) {
    $tables[] = $row[0];
}

echo "Tables: " . implode(", ", $tables) . "\n";

if (in_array('supplier', $tables)) {
    echo "Supplier table exists.\n";
} else {
    echo "Supplier table DOES NOT exist.\n";
}

if (in_array('purchase', $tables)) {
    echo "Purchase table already exists.\n";
} else {
    echo "Creating purchase table...\n";
    $sql = "CREATE TABLE `purchase` (
      `id` bigint NOT NULL AUTO_INCREMENT,
      `createdBy` varchar(255) DEFAULT NULL,
      `createdOn` datetime(6) DEFAULT NULL,
      `modifiedBy` varchar(255) DEFAULT NULL,
      `modifiedOn` datetime(6) DEFAULT NULL,
      `actualAmountSum` float NOT NULL,
      `orderDate` date DEFAULT NULL,
      `orderNo` varchar(255) DEFAULT NULL,
      `orderStatus` varchar(255) DEFAULT NULL,
      `paidAmountSum` float NOT NULL,
      `quoteAmountSum` float NOT NULL,
      `supplier` bigint DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `FKikg2365x5p0ctt5j10o55mh6n` (`supplier`),
      CONSTRAINT `FKikg2365x5p0ctt5j10o55mh6n` FOREIGN KEY (`supplier`) REFERENCES `supplier` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf32;";
    
    if (mysqli_query($conn, $sql)) {
        echo "Purchase table created successfully.\n";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "\n";
    }
}
?>
