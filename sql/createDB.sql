SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `normdb` ;
CREATE SCHEMA IF NOT EXISTS `normdb` ;
USE `normdb` ;

-- -----------------------------------------------------
-- Table `normdb`.`tStudyType`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tStudyType` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tStudyType` (
  `idStudyType` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`idStudyType`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tSpecies`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tSpecies` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tSpecies` (
  `idSpecies` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`idSpecies`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tAssayType`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tAssayType` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tAssayType` (
  `idAssayType` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`idAssayType`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tDomains`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tDomains` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tDomains` (
  `idDomain` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`idDomain`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tArrayPlatform`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tArrayPlatform` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tArrayPlatform` (
  `idArrayPlatform` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `arrayType` VARCHAR(45) NOT NULL,
  `annoType` VARCHAR(75) NOT NULL,
  PRIMARY KEY (`idArrayPlatform`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tStudy`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tStudy` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tStudy` (
  `idStudy` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `curator` VARCHAR(45) NULL,
  `description` TEXT NULL,
  `source` VARCHAR(45) NULL,
  `submissionDate` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `idStudyType` INT NOT NULL,
  `idMainSpecies` INT NOT NULL,
  `idAssayType` INT NOT NULL,
  `idDomain` INT NOT NULL,
  `idArrayPlatform` INT NULL,
  PRIMARY KEY (`idStudy`),
  INDEX `fk_tStudy_tStudyTypes_idx` (`idStudyType` ASC),
  INDEX `fk_tStudy_tSpecies1_idx` (`idMainSpecies` ASC),
  INDEX `fk_tStudy_tExperimentType1_idx` (`idAssayType` ASC),
  INDEX `fk_tStudy_tDomains1_idx` (`idDomain` ASC),
  INDEX `fk_tStudy_tArrayPlatform1_idx` (`idArrayPlatform` ASC),
  CONSTRAINT `fk_tStudy_tStudyTypes`
    FOREIGN KEY (`idStudyType`)
    REFERENCES `normdb`.`tStudyType` (`idStudyType`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tStudy_tSpecies1`
    FOREIGN KEY (`idMainSpecies`)
    REFERENCES `normdb`.`tSpecies` (`idSpecies`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tStudy_tExperimentType1`
    FOREIGN KEY (`idAssayType`)
    REFERENCES `normdb`.`tAssayType` (`idAssayType`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tStudy_tDomains1`
    FOREIGN KEY (`idDomain`)
    REFERENCES `normdb`.`tDomains` (`idDomain`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tStudy_tArrayPlatform1`
    FOREIGN KEY (`idArrayPlatform`)
    REFERENCES `normdb`.`tArrayPlatform` (`idArrayPlatform`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tCompound`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tCompound` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tCompound` (
  `idCompound` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `casNumber` VARCHAR(45) NOT NULL,
  `abbreviation` VARCHAR(45) NULL,
  `officialName` VARCHAR(45) NULL,
  PRIMARY KEY (`idCompound`),
  UNIQUE INDEX `casNumber_UNIQUE` (`casNumber` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tSampleType`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tSampleType` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tSampleType` (
  `idSampleType` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(75) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`idSampleType`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tSamples`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tSamples` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tSamples` (
  `idSample` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `idStudy` INT NOT NULL,
  `idSampleType` INT NOT NULL,
  `sxsName` VARCHAR(75) NULL,
  `name` VARCHAR(100) NOT NULL,
  `submissionDate` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `idCompound` INT NOT NULL,
  PRIMARY KEY (`idSample`),
  INDEX `fk_tSamples_tCompound1_idx` (`idCompound` ASC),
  INDEX `fk_tSamples_tStudy1_idx` (`idStudy` ASC),
  INDEX `fk_tSamples_tSampleType1_idx` (`idSampleType` ASC),
  UNIQUE INDEX `study_name_UNIQUE` (`name` ASC, `idStudy` ASC),
  INDEX `sampleNameIndex` (`name` ASC),
  CONSTRAINT `fk_tSamples_tCompound1`
    FOREIGN KEY (`idCompound`)
    REFERENCES `normdb`.`tCompound` (`idCompound`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tSamples_tStudy1`
    FOREIGN KEY (`idStudy`)
    REFERENCES `normdb`.`tStudy` (`idStudy`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tSamples_tSampleType1`
    FOREIGN KEY (`idSampleType`)
    REFERENCES `normdb`.`tSampleType` (`idSampleType`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tCompoundSynonyms`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tCompoundSynonyms` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tCompoundSynonyms` (
  `idCompound` INT NOT NULL,
  `synonym` VARCHAR(100) NOT NULL,
  INDEX `fk_tCompoundSynonyms_tCompound1_idx` (`idCompound` ASC),
  CONSTRAINT `fk_tCompoundSynonyms_tCompound1`
    FOREIGN KEY (`idCompound`)
    REFERENCES `normdb`.`tCompound` (`idCompound`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tDataType`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tDataType` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tDataType` (
  `idDataType` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(75) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`idDataType`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tAttributes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tAttributes` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tAttributes` (
  `idSample` INT UNSIGNED NOT NULL,
  `idDataType` INT NOT NULL,
  `value` VARCHAR(50) NOT NULL,
  INDEX `fk_tAttributes_tSamples1_idx` (`idSample` ASC),
  INDEX `fk_tAttributes_tDataType1_idx` (`idDataType` ASC),
  CONSTRAINT `fk_tAttributes_tSamples1`
    FOREIGN KEY (`idSample`)
    REFERENCES `normdb`.`tSamples` (`idSample`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tAttributes_tDataType1`
    FOREIGN KEY (`idDataType`)
    REFERENCES `normdb`.`tDataType` (`idDataType`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tProbes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tProbes` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tProbes` (
  `idProbe` INT NOT NULL AUTO_INCREMENT,
  `nuID` VARCHAR(75) NOT NULL,
  `ilmnGene` VARCHAR(45) NULL,
  `probeID` VARCHAR(45) NULL,
  `entrezGeneID` VARCHAR(45) NULL,
  `geneSymbol` VARCHAR(45) NULL,
  `geneName` VARCHAR(45) NULL,
  `accessionName` VARCHAR(45) NULL,
  PRIMARY KEY (`idProbe`),
  UNIQUE INDEX `nuID_UNIQUE` (`nuID` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tNormAnalysis`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tNormAnalysis` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tNormAnalysis` (
  `idNormAnalysis` INT NOT NULL AUTO_INCREMENT,
  `idStudy` INT NOT NULL,
  `description` TEXT NULL,
  `normType` VARCHAR(10) NULL,
  `bgCorrectionMethod` VARCHAR(20) NULL,
  `varStabMethod` VARCHAR(20) NULL,
  `normMethod` VARCHAR(20) NULL,
  `filterThreshold` FLOAT NULL,
  PRIMARY KEY (`idNormAnalysis`),
  INDEX `fk_tNormAnalysis_tStudy1_idx` (`idStudy` ASC),
  CONSTRAINT `fk_tNormAnalysis_tStudy1`
    FOREIGN KEY (`idStudy`)
    REFERENCES `normdb`.`tStudy` (`idStudy`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tNormedExpression`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tNormedExpression` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tNormedExpression` (
  `idNormExpression` INT NOT NULL AUTO_INCREMENT,
  `expressionValue` FLOAT NOT NULL,
  `idSample` INT UNSIGNED NOT NULL,
  `idProbe` INT NOT NULL,
  `idNormAnalysis` INT NOT NULL,
  INDEX `fk_tNormedExpression_tProbes1_idx` (`idProbe` ASC),
  INDEX `fk_tNormedExpression_tNormAnalysis1_idx` (`idNormAnalysis` ASC),
  PRIMARY KEY (`idNormExpression`),
  INDEX `fk_tNormedExpression_tSamples1_idx` (`idSample` ASC),
  CONSTRAINT `fk_tNormedExpression_tProbes1`
    FOREIGN KEY (`idProbe`)
    REFERENCES `normdb`.`tProbes` (`idProbe`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tNormedExpression_tNormAnalysis1`
    FOREIGN KEY (`idNormAnalysis`)
    REFERENCES `normdb`.`tNormAnalysis` (`idNormAnalysis`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tNormedExpression_tSamples1`
    FOREIGN KEY (`idSample`)
    REFERENCES `normdb`.`tSamples` (`idSample`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tSampleSummary`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tSampleSummary` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tSampleSummary` (
  `idSummary` INT NOT NULL AUTO_INCREMENT,
  `idNormAnalysis` INT NOT NULL,
  `idSample` INT UNSIGNED NOT NULL,
  `meanSample` FLOAT NULL,
  `standardError` FLOAT NULL,
  `detectionRate_01` FLOAT NULL,
  `distanceToMeanSample` FLOAT NULL,
  `normed` TINYINT(1) NULL,
  INDEX `fk_tSampleSummary_tSamples1_idx` (`idSample` ASC),
  INDEX `fk_tSampleSummary_tNormAnalysis1_idx` (`idNormAnalysis` ASC),
  PRIMARY KEY (`idSummary`),
  CONSTRAINT `fk_tSampleSummary_tSamples1`
    FOREIGN KEY (`idSample`)
    REFERENCES `normdb`.`tSamples` (`idSample`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tSampleSummary_tNormAnalysis1`
    FOREIGN KEY (`idNormAnalysis`)
    REFERENCES `normdb`.`tNormAnalysis` (`idNormAnalysis`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tDirectory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tDirectory` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tDirectory` (
  `idDirectory` INT NOT NULL AUTO_INCREMENT,
  `folderName` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`idDirectory`),
  UNIQUE INDEX `folderName_UNIQUE` (`folderName` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tFileType`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tFileType` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tFileType` (
  `idFileType` INT NOT NULL AUTO_INCREMENT,
  `idDirectory` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `searchOn` VARCHAR(100) NULL,
  PRIMARY KEY (`idFileType`),
  INDEX `fk_tFileType_tDirectory1_idx` (`idDirectory` ASC),
  CONSTRAINT `fk_tFileType_tDirectory1`
    FOREIGN KEY (`idDirectory`)
    REFERENCES `normdb`.`tDirectory` (`idDirectory`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tStatistics`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tStatistics` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tStatistics` (
  `idStatistics` INT NOT NULL AUTO_INCREMENT,
  `idNormAnalysis` INT NOT NULL,
  `groupedOn` VARCHAR(100) NULL,
  `submissionDate` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `description` TEXT NULL,
  PRIMARY KEY (`idStatistics`),
  INDEX `fk_tStatistics_tNormAnalysis1_idx` (`idNormAnalysis` ASC),
  CONSTRAINT `fk_tStatistics_tNormAnalysis1`
    FOREIGN KEY (`idNormAnalysis`)
    REFERENCES `normdb`.`tNormAnalysis` (`idNormAnalysis`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tFiles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tFiles` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tFiles` (
  `idFile` INT NOT NULL AUTO_INCREMENT,
  `idStudy` INT NOT NULL,
  `idFileType` INT NOT NULL,
  `fileName` VARCHAR(150) NOT NULL,
  `idNorm` INT NULL,
  `idStatistics` INT NULL,
  INDEX `fk_tFiles_tStudy1_idx` (`idStudy` ASC),
  INDEX `fk_tFiles_tFileType1_idx` (`idFileType` ASC),
  PRIMARY KEY (`idFile`),
  INDEX `fk_tFiles_tNormAnalysis1_idx` (`idNorm` ASC),
  INDEX `fk_tFiles_tStatistics1_idx` (`idStatistics` ASC),
  CONSTRAINT `fk_tFiles_tStudy1`
    FOREIGN KEY (`idStudy`)
    REFERENCES `normdb`.`tStudy` (`idStudy`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tFiles_tFileType1`
    FOREIGN KEY (`idFileType`)
    REFERENCES `normdb`.`tFileType` (`idFileType`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_tFiles_tNormAnalysis1`
    FOREIGN KEY (`idNorm`)
    REFERENCES `normdb`.`tNormAnalysis` (`idNormAnalysis`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tFiles_tStatistics1`
    FOREIGN KEY (`idStatistics`)
    REFERENCES `normdb`.`tStatistics` (`idStatistics`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `normdb`.`tJobStatus`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `normdb`.`tJobStatus` ;

CREATE TABLE IF NOT EXISTS `normdb`.`tJobStatus` (
  `idJob` INT NOT NULL AUTO_INCREMENT,
  `idStudy` INT NOT NULL,
  `name` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `status` TINYINT NULL,
  `statusMessage` TEXT NULL,
  `submissionDate` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idJob`),
  INDEX `fk_tJobStatus_tStudy1_idx` (`idStudy` ASC),
  CONSTRAINT `fk_tJobStatus_tStudy1`
    FOREIGN KEY (`idStudy`)
    REFERENCES `normdb`.`tStudy` (`idStudy`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

USE `normdb` ;

-- -----------------------------------------------------
-- Placeholder table for view `normdb`.`vStudyWithTypeNames`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `normdb`.`vStudyWithTypeNames` (`idStudy` INT, `title` INT, `curator` INT, `description` INT, `source` INT, `submissionDate` INT, `idStudyType` INT, `idMainSpecies` INT, `idAssayType` INT, `idDomain` INT, `idArrayPlatform` INT, `studyTypeName` INT, `assayName` INT, `speciesName` INT, `domainName` INT, `platFormName` INT, `arrayType` INT, `annoType` INT);

-- -----------------------------------------------------
-- Placeholder table for view `normdb`.`vSamplesWithInfoNames`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `normdb`.`vSamplesWithInfoNames` (`idSample` INT, `idStudy` INT, `title` INT, `name` INT, `sxsName` INT, `submissionDate` INT, `idCompound` INT, `compoundName` INT, `casNumber` INT, `typeName` INT);

-- -----------------------------------------------------
-- Placeholder table for view `normdb`.`vSamplesWithAttributes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `normdb`.`vSamplesWithAttributes` (`idSample` INT, `idStudy` INT, `attrValue` INT, `dataTypeName` INT, `idDataType` INT);

-- -----------------------------------------------------
-- Placeholder table for view `normdb`.`vFilesWithInfo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `normdb`.`vFilesWithInfo` (`idFile` INT, `idStudy` INT, `idFileType` INT, `fileName` INT, `idNorm` INT, `idStatistics` INT, `name` INT, `description` INT, `idDirectory` INT, `searchOn` INT, `folderName` INT);

-- -----------------------------------------------------
-- Placeholder table for view `normdb`.`vFileTypesWithInfo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `normdb`.`vFileTypesWithInfo` (`idFileType` INT, `name` INT, `description` INT, `idDirectory` INT, `searchOn` INT, `folderName` INT);

-- -----------------------------------------------------
-- Placeholder table for view `normdb`.`vExpressionWithInfo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `normdb`.`vExpressionWithInfo` (`idNormExpression` INT, `expressionValue` INT, `sampleName` INT, `sxsName` INT, `typeName` INT, `idNormAnalysis` INT, `idProbe` INT, `geneName` INT, `entrezGeneID` INT);

-- -----------------------------------------------------
-- View `normdb`.`vStudyWithTypeNames`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `normdb`.`vStudyWithTypeNames` ;
DROP TABLE IF EXISTS `normdb`.`vStudyWithTypeNames`;
USE `normdb`;
CREATE  OR REPLACE SQL SECURITY INVOKER VIEW `vStudyWithTypeNames` AS
    SELECT 
        tStudy . *,
        tStudyType.name AS studyTypeName,
        tAssayType.name AS assayName,
        tSpecies.name AS speciesName,
        tDomains.name AS domainName,
		tArrayPlatform.name AS platFormName,
		tArrayPlatform.arrayType AS arrayType,
		tArrayPlatform.annoType AS annoType
    FROM
        tStudy
            INNER JOIN
        tStudyType ON tStudy.idStudyType = tStudyType.idStudyType
            INNER JOIN
        tAssayType ON tStudy.idAssayType = tAssayType.idAssayType
            INNER JOIN
        tSpecies ON tStudy.idMainSpecies = tSpecies.idSpecies
            INNER JOIN
        tDomains ON tStudy.idDomain = tDomains.idDomain
            INNER JOIN
        tArrayPlatform ON tStudy.idArrayPlatform = tArrayPlatform.idArrayPlatform;

-- -----------------------------------------------------
-- View `normdb`.`vSamplesWithInfoNames`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `normdb`.`vSamplesWithInfoNames` ;
DROP TABLE IF EXISTS `normdb`.`vSamplesWithInfoNames`;
USE `normdb`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `normdb`@`%` 
    SQL SECURITY INVOKER
VIEW `vSamplesWithInfoNames` AS
    select 
        `tSamples`.`idSample` AS `idSample`,
        `tSamples`.`idStudy` AS `idStudy`,
        `tStudy`.`title` AS `title`,
        `tSamples`.`name` AS `name`,
		`tSamples`.`sxsName` AS `sxsName`,
        `tSamples`.`submissionDate` AS `submissionDate`,
        `tSamples`.`idCompound` AS `idCompound`,
        `tCompound`.`name` AS `compoundName`,
        `tCompound`.`casNumber` AS `casNumber`,
        `tSampleType`.`name` AS `typeName`
    from
        `tSamples`
        join `tCompound` ON `tSamples`.`idCompound` = `tCompound`.`idCompound`
        join `tStudy` ON `tSamples`.`idStudy` = `tStudy`.`idStudy`
        join `tSampleType` ON `tSamples`.`idSampleType` = `tSampleType`.`idSampleType`;

-- -----------------------------------------------------
-- View `normdb`.`vSamplesWithAttributes`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `normdb`.`vSamplesWithAttributes` ;
DROP TABLE IF EXISTS `normdb`.`vSamplesWithAttributes`;
USE `normdb`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `normdb`@`%` 
    SQL SECURITY INVOKER
VIEW `vSamplesWithAttributes` AS
    select 
        `tSamples`.`idSample` AS `idSample`,
        `tSamples`.`idStudy` AS `idStudy`,
        `tAttributes`.`value` AS `attrValue`,
        `tDataType`.`name` AS `dataTypeName`,
		`tDataType`.`idDataType` AS `idDataType`
    from
        ((`tSamples`
        join `tAttributes` ON ((`tSamples`.`idSample` = `tAttributes`.`idSample`)))
        join `tDataType` ON ((`tAttributes`.`idDataType` = `tDataType`.`idDataType`)));

-- -----------------------------------------------------
-- View `normdb`.`vFilesWithInfo`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `normdb`.`vFilesWithInfo` ;
DROP TABLE IF EXISTS `normdb`.`vFilesWithInfo`;
USE `normdb`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `normdb`@`%` 
    SQL SECURITY INVOKER
VIEW `vFilesWithInfo` AS
    select 
        `tFiles`.`idFile` AS `idFile`,
        `tFiles`.`idStudy` AS `idStudy`,
        `tFiles`.`idfileType` AS `idFileType`,
        `tFiles`.`fileName` AS `fileName`,
        `tFiles`.`idNorm` AS `idNorm`,
		`tFiles`.`idStatistics` AS `idStatistics`,
        `tFileType`.`name` AS `name`,
        `tFileType`.`description` AS `description`,
        `tFileType`.`idDirectory` AS `idDirectory`,
        `tFileType`.`searchOn` AS `searchOn`,
		tDirectory.folderName AS `folderName`
    from
        `tFiles`
            join
        `tFileType` ON `tFiles`.`idFileType` = `tFileType`.`idFileType`
            join
        `tDirectory` ON `tFileType`.`idDirectory` = `tDirectory`.`idDirectory`
;

-- -----------------------------------------------------
-- View `normdb`.`vFileTypesWithInfo`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `normdb`.`vFileTypesWithInfo` ;
DROP TABLE IF EXISTS `normdb`.`vFileTypesWithInfo`;
USE `normdb`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `normdb`@`%` 
    SQL SECURITY INVOKER
VIEW `vFileTypesWithInfo` AS
    select 
		`tFileType`.`idFileType` AS `idFileType`,
        `tFileType`.`name` AS `name`,
        `tFileType`.`description` AS `description`,
        `tFileType`.`idDirectory` AS `idDirectory`,
        `tFileType`.`searchOn` AS `searchOn`,
        `tDirectory`.`folderName` AS `folderName`
    from
        (`tFileType`
        join `tDirectory` ON ((`tFileType`.`idDirectory` = `tDirectory`.`idDirectory`)));

-- -----------------------------------------------------
-- View `normdb`.`vExpressionWithInfo`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `normdb`.`vExpressionWithInfo` ;
DROP TABLE IF EXISTS `normdb`.`vExpressionWithInfo`;
USE `normdb`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `normdb`@`%` 
    SQL SECURITY INVOKER
VIEW `vExpressionWithInfo` AS
    select 
		`tNormedExpression`.`idNormExpression` AS `idNormExpression`,
        `tNormedExpression`.`expressionValue` AS `expressionValue`,
        `tSamples`.`name` AS `sampleName`,
		`tSamples`.`sxsName` AS `sxsName`,
		`tSampleType`.`name` AS `typeName`,
        `tNormAnalysis`.`idNormAnalysis` AS `idNormAnalysis`,
        `tProbes`.`idProbe` AS `idProbe`,
        `tProbes`.`geneName` AS `geneName`,
        `tProbes`.`entrezGeneID` AS `entrezGeneID`
    from
        `tNormedExpression`
            join
        `tSamples` ON `tNormedExpression`.`idSample` = `tSamples`.`idSample`
            join
        `tSampleType` ON `tSamples`.`idSampleType` = `tSampleType`.`idSampleType`   
         join
        `tNormAnalysis` ON `tNormAnalysis`.`idNormAnalysis` = `tNormedExpression`.`idNormAnalysis`
         join
        `tProbes` ON `tProbes`.`idProbe` = `tNormedExpression`.`idProbe`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
