
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