
CREATE TABLE `customer` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `active` bit(1) NOT NULL,
  `customerId` varchar(255) DEFAULT NULL,
  `emailId` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phoneNo1` varchar(255) DEFAULT NULL,
  `phoneNo2` varchar(255) DEFAULT NULL,
  `whatsAppNo` varchar(255) DEFAULT NULL,
  `address` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_f4ujgq5ch50wl6lx48iu6wcvv` (`address`),
  CONSTRAINT `FKfn5r4mug085lanufp9msc1q6n` FOREIGN KEY (`address`) REFERENCES `address` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2716 DEFAULT CHARSET=utf32;

CREATE TABLE `address` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `line1` varchar(255) DEFAULT NULL,
  `line2` varchar(255) DEFAULT NULL,
  `zipCode` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32;


CREATE TABLE `brand` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `brandName` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_8mw73aryipaafhvqpgt1ioud4` (`brandName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

CREATE TABLE `model` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `model` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_rnt7dvqqcy5rm8oa4cp32bk11` (`model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

CREATE TABLE `spares` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `active` bit(1) NOT NULL,
  `partNo` varchar(255) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `rackNumber` varchar(255) DEFAULT NULL,
  `spareName` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

CREATE TABLE `machine` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `active` bit(1) NOT NULL,
  `assembeldByUs` bit(1) NOT NULL,
  `machineName` varchar(50) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `machineType` bigint DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

CREATE TABLE `supplier` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `active` bit(1) NOT NULL,
  `emailId` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phoneNo1` varchar(255) DEFAULT NULL,
  `phoneNo2` varchar(255) DEFAULT NULL,
  `whatsAppNo` varchar(255) DEFAULT NULL,
  `address` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_od83j1diqux7fbobjyjddllvh` (`address`),
  CONSTRAINT `FK15ewpkpum7cnphgckhbpyfjo0` FOREIGN KEY (`address`) REFERENCES `address` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

CREATE TABLE `purchase` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

CREATE TABLE `purchaseitems` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `deleted` bit(1) NOT NULL,
  `gstPercentage` float NOT NULL,
  `gstValue` float NOT NULL,
  `itemName` varchar(255) DEFAULT NULL,
  `orderedQuantity` int NOT NULL,
  `receivedQuantity` int NOT NULL,
  `sellingPricePerQtyWOGSt` float NOT NULL,
  `totalPriceWithGst` float NOT NULL,
  `totalPriceWithoutGst` float NOT NULL,
  `brand` bigint DEFAULT NULL,
  `machine` bigint DEFAULT NULL,
  `model` bigint DEFAULT NULL,
  `spare` bigint DEFAULT NULL,
  `purchase` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKdqjd5p7se2twxxetscav6mjkq` (`brand`),
  KEY `FK3n0y31ts5kx8rq4iryy2gah2l` (`machine`),
  KEY `FKljl6fid801qifaqkmbf348n7r` (`model`),
  KEY `FKhns7p9cmfxfxlp6egjuhf8wqs` (`spare`),
  KEY `FK3joimr4evmw7yuxmrgo4elk4` (`purchase`),
  CONSTRAINT `FK3joimr4evmw7yuxmrgo4elk4` FOREIGN KEY (`purchase`) REFERENCES `purchase` (`id`),
  CONSTRAINT `FK3n0y31ts5kx8rq4iryy2gah2l` FOREIGN KEY (`machine`) REFERENCES `machine` (`id`),
  CONSTRAINT `FKdqjd5p7se2twxxetscav6mjkq` FOREIGN KEY (`brand`) REFERENCES `brand` (`id`),
  CONSTRAINT `FKhns7p9cmfxfxlp6egjuhf8wqs` FOREIGN KEY (`spare`) REFERENCES `spares` (`id`),
  CONSTRAINT `FKljl6fid801qifaqkmbf348n7r` FOREIGN KEY (`model`) REFERENCES `model` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

CREATE TABLE `stock` (
    `id` varchar(255) NOT NULL,
    `actualPricePerQty` float NOT NULL,
    `actualPricePerUnit` float NOT NULL,
    `availableQty` int NOT NULL,
    `barCode` varchar(255) DEFAULT NULL,
    `gstPercentage` float NOT NULL,
    `itemName` varchar(255) DEFAULT NULL,
    `quantity` int NOT NULL,
    `selled` bit(1) NOT NULL,
    `selledPricePerUnit` double NOT NULL,
    `sellingPricePerQty` float NOT NULL,
    `sellingPricePerUnit` float NOT NULL,
    `serialNo` varchar(255) DEFAULT NULL,
    `unit` int NOT NULL,
    `warrantyInMonths` int NOT NULL,
    `brand` bigint DEFAULT NULL,
    `machine` bigint DEFAULT NULL,
    `model` bigint DEFAULT NULL,
    `purchaseItem` bigint DEFAULT NULL,
    `spare` bigint DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `FK17lg7477nvywgyva1qnfan255` (`brand`),
    KEY `FKdn32ck0k6jt4dg9t7vl28ba8u` (`machine`),
    KEY `FKh2407ud8pbmwnxo4q65xftumf` (`model`),
    KEY `FKeifebnupfgukc2oojtse960ii` (`purchaseItem`),
    KEY `FK316sfaegiisdk4bcna59ynqle` (`spare`),
    CONSTRAINT `FK17lg7477nvywgyva1qnfan255` FOREIGN KEY (`brand`) REFERENCES `brand` (`id`),
    CONSTRAINT `FK316sfaegiisdk4bcna59ynqle` FOREIGN KEY (`spare`) REFERENCES `spares` (`id`),
    CONSTRAINT `FKdn32ck0k6jt4dg9t7vl28ba8u` FOREIGN KEY (`machine`) REFERENCES `machine` (`id`),
    CONSTRAINT `FKeifebnupfgukc2oojtse960ii` FOREIGN KEY (`purchaseItem`) REFERENCES `purchaseitems` (`id`),
    CONSTRAINT `FKh2407ud8pbmwnxo4q65xftumf` FOREIGN KEY (`model`) REFERENCES `model` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

CREATE TABLE `sales` (
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
  `customer` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKt3sxcyawyfdjcr7wanolo98so` (`customer`),
  CONSTRAINT `FKt3sxcyawyfdjcr7wanolo98so` FOREIGN KEY (`customer`) REFERENCES `customer` (`id`)

CREATE TABLE `sales_items` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `salesId` bigint NOT NULL,
  `itemName` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `serialNo` varchar(255) DEFAULT NULL,
  `rackNo` varchar(255) DEFAULT NULL,
  `quantity` int DEFAULT 1,
  `pricePerQty` float DEFAULT 0,
  `gstPercentage` float DEFAULT 0,
  `totalPrice` float DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_sales_items_sales` FOREIGN KEY (`salesId`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf32;


CREATE TABLE `salesitems` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `deleted` bit(1) NOT NULL,
  `gstPercentage` float NOT NULL,
  `gstValue` float NOT NULL,
  `itemName` varchar(255) DEFAULT NULL,
  `pricePerQty` float NOT NULL,
  `quantity` int NOT NULL,
  `serialNo` varchar(255) DEFAULT NULL,
  `totalPrice` float NOT NULL,
  `machine` bigint DEFAULT NULL,
  `spare` bigint DEFAULT NULL,
  `stock` varchar(255) DEFAULT NULL,
  `sales` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKjnb1apgqbbekyalao9g0k39xy` (`machine`),
  KEY `FKgiesap1p06s6juylbni1rqnb0` (`spare`),
  KEY `FKp2olmuodj2he18veht4qj1o6b` (`stock`),
  KEY `FK3oqcphfy6eav6e7wtqnusgevt` (`sales`),
  CONSTRAINT `FK3oqcphfy6eav6e7wtqnusgevt` FOREIGN KEY (`sales`) REFERENCES `sales` (`id`),
  CONSTRAINT `FKgiesap1p06s6juylbni1rqnb0` FOREIGN KEY (`spare`) REFERENCES `spares` (`id`),
  CONSTRAINT `FKjnb1apgqbbekyalao9g0k39xy` FOREIGN KEY (`machine`) REFERENCES `machine` (`id`),
  CONSTRAINT `FKp2olmuodj2he18veht4qj1o6b` FOREIGN KEY (`stock`) REFERENCES `stock` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7835 DEFAULT CHARSET=utf32;

CREATE TABLE `jobcard` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `actualAmountSum` float NOT NULL,
  `cardNo` varchar(255) DEFAULT NULL,
  `completed` bit(1) NOT NULL,
  `completedDate` date DEFAULT NULL,
  `delivered` bit(1) NOT NULL,
  `deliveryDate` date DEFAULT NULL,
  `givenDate` date DEFAULT NULL,
  `jobCategory` varchar(255) DEFAULT NULL,
  `jobStatus` varchar(255) DEFAULT NULL,
  `laborCharge` float NOT NULL,
  `quoteAmountSum` float NOT NULL,
  `receivedAmountSum` float NOT NULL,
  `customer` bigint DEFAULT NULL,
  `employee` bigint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FKr7qhnw1v6mamxow90egb5fvkb` (`customer`),
  KEY `FK5gaox2htdx5dlyfbmj34ysot1` (`employee`),
  CONSTRAINT `FK5gaox2htdx5dlyfbmj34ysot1` FOREIGN KEY (`employee`) REFERENCES `employee` (`id`),
  CONSTRAINT `FKr7qhnw1v6mamxow90egb5fvkb` FOREIGN KEY (`customer`) REFERENCES `customer` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2619 DEFAULT CHARSET=utf32;

CREATE TABLE `employee` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `active` bit(1) NOT NULL,
  `city` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `empId` varchar(255) DEFAULT NULL,
  `employmentType` varchar(255) DEFAULT NULL,
  `exitDate` date DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `joinedDate` date DEFAULT NULL,
  `line1` varchar(255) DEFAULT NULL,
  `line2` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phoneNo1` varchar(255) DEFAULT NULL,
  `phoneNo2` varchar(255) DEFAULT NULL,
  `zipCode` varchar(255) DEFAULT NULL,
  `credential` bigint DEFAULT NULL,
  `aadhaarNo` varchar(255) DEFAULT NULL,
  `aadharCardPhoto` varchar(255) DEFAULT NULL,
  `drivingLicence` varchar(255) DEFAULT NULL,
  `drivingLicencePhoto` varchar(255) DEFAULT NULL,
  `profilePhoto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_1yxxakvyi7blmiydsg8415xwd` (`credential`),
  CONSTRAINT `FK1c7h2omesuyjmly5k29regua` FOREIGN KEY (`credential`) REFERENCES `credential` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf32;


CREATE TABLE `credential` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `createdBy` varchar(255) DEFAULT NULL,
  `createdOn` datetime(6) DEFAULT NULL,
  `modifiedBy` varchar(255) DEFAULT NULL,
  `modifiedOn` datetime(6) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UK_p961pvq98pc0pkxa5qh22h7tp` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf32;