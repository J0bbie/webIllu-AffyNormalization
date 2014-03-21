INSERT INTO `normdb`.`tStudyType` (`name`) VALUES ('Single Dose Toxicity');
INSERT INTO `normdb`.`tStudyType` (`name`) VALUES ('Genotoxicity');

INSERT INTO `normdb`.`tArrayPlatform` (`name`, annoType, arrayType) VALUES ('Illumina BeadChip - HumanHT-12_V4_0_R2_15002873_B', 'HumanHT-12_V4_0_R2_15002873_B', 'HumanHT-12');

INSERT INTO `normdb`.`tDomains` (`name`) VALUES ('Liver toxicity');
INSERT INTO `normdb`.`tDomains` (`name`) VALUES ('Kidney toxicity');
INSERT INTO `normdb`.`tDomains` (`name`) VALUES ('Developmental toxicity');

INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('raw', 'Folder to hold all the raw data.');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('sampleAnnotation', 'Folder to hold the annotation of the samples. ');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('expressionData/raw', 'Folder to hold all the expression data. (Provided by SXS)');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('expressionData/normed', 'Folder to hold all the normalized expression data.');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('statistics/', 'Folder to hold all the results from QC of raw data.');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('Unknown', 'Could not determine the filetype!');

INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Sample File (Multiple)', 'File that contains (multiple) samples of a study.', 2, 'sampleFile');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Unknown', 'Could not determine the filetype!', 6, 'unknown');

INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Control Gene Profile', 'The expression profiles of the (control) genes. (SXS)', 3, 'Control_Gene_Profile');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Control Probe Profile', 'The expression profiles of the (control)  probes. (SXS)', 3, 'Control_Probe_Profile');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Excluded and Imputed Probes', 'The excluded and imputed probes by SXS.', 3, 'Excluded_and_Imputed');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Sample Gene Profile', 'The expression profiles of the genes. (SXS)', 3, 'Sample_Gene_Profile');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Sample Probe Profile', 'The expression profiles of the probes. (SXS)', 3, 'Sample_Probe_Profile');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Samples Table', 'Information about the samples on the Array (SXS)', 3, 'Samples_Table');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('ServiceXS RNAsample submission', 'Information about the RNA submission to SXS', 3, 'ServiceXS_RNAsample_submission');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('QC Report', 'QC report from SXS', 3, 'QC_Report');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Data Inspection Summary', 'Data inspection summary (SXS)', 3, 'Data_Inspection_Summary');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Raw Illumina Data LumiBatch Object', 'Raw Illumina data from this pipeline in a LumiBatch R Object.', 3, 'rawData.Rdata');

INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Illumina Data', 'Normalized Illumina data from this pipeline.', 4, 'normData_lumi');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Illumina Data LumiBatch Object', 'Normalized Illumina data from this pipeline in a LumiBatch R Object.', 4, 'normData.Rdata');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Illumina Data with Entrez', 'Normalized Illumina data from this pipeline merged with entrez genes.', 4, 'normData_lumi_merged');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Illumina Data Filtered', 'Normalized Illumina data from this pipeline with probes/genes with a low expression filtered.', 4, 'normData_Filtered');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Summary raw data', 'Summary of raw data statistics.', 4, 'summary_rawData');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Summary norm data', 'Summary of norm data statistics.', 4, 'summary_normData');

INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Boxplot of amplitudes (raw data)', 'Boxplot of amplitudes', 5, 'RAW_boxplot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Covariance plot (raw data)', 'Plot density for coefficient of varience for intensities', 5, 'RAW_cv_plot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Density histogram (raw data)', 'Plot density histogram for intensities', 5, 'RAW_density');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Data array correlation (raw data)', 'Correlation plot for array correlation', 5, 'RAW_dataArrayCorrelation');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Cluster plot (raw data)', 'Cluster plot based on defined group', 5, 'RAW_dataCluster');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('PCA (raw data)', 'Plot density for coefficient of varience for intensities', 5, 'RAW_dataPCA');

INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Boxplot of amplitudes (norm data)', 'Boxplot of amplitudes', 5, 'NORM_boxplot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Covariance plot (norm data)', 'Plot density for coefficient of varience for intensities', 5, 'NORM_cv_plot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Density histogram (norm data)', 'Plot density histogram for intensities', 5, 'NORM_density');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Data array correlation (norm data)', 'Correlation plot for array correlation', 5, 'NORM_dataArrayCorrelation');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Cluster plot (norm data)', 'Cluster plot based on defined group', 5, 'NORM_dataCluster');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('PCA (norm data)', 'Plot density for coefficient of varience for intensities', 5, 'NORM_dataPCA');

INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('StatSubsetFile', 'File containing the sampleNames used in the statistics if a subset was used.', 5, 'statSubsetFile');

INSERT INTO `normdb`.`tSampleType` (`name`, `description`) VALUES ('posControl', 'Positive control');
INSERT INTO `normdb`.`tSampleType` (`name`, `description`) VALUES ('negControl', 'Negative control');
INSERT INTO `normdb`.`tSampleType` (`name`, `description`) VALUES ('sample', 'Study sample');

INSERT INTO `normdb`.`tSpecies` (`name`) VALUES ('Human');
INSERT INTO `normdb`.`tSpecies` (`name`) VALUES ('Mouse');
INSERT INTO `normdb`.`tSpecies` (`name`) VALUES ('Rat');

INSERT INTO `normdb`.`tAssayType` (`name`) VALUES ('In vivo');
INSERT INTO `normdb`.`tAssayType` (`name`) VALUES ('Ex vivo');
INSERT INTO `normdb`.`tAssayType` (`name`) VALUES ('In vitro');

INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('sampleName', 'Recognizable name for the sample');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('compoundName', 'Name of the compound.');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('compoundCAS', 'CAS number of the compound.');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('sampleType', 'Type of the sample. E.g. posControl/negControl etc.');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('noel', 'No observed effect level');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('LD50', 'Lethal Dose for 50% of subjects');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('noAel', 'No observed adverse effect level');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('Timepoint Incubation', 'timepoint on which incubation occured.');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('Organ', 'Targeted Organ.');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('TNO Sample ID', 'sample ID given by TNO for assay.');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('Replicate #', 'Number of the biological/technical replicate.');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('Cluster', 'Groups in which the samples are to be clustered.');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('concentrationCompound_mM', 'Concentration of the compound in mM.');

#Read in a tab-delimited file with compounds + cas + name
LOAD DATA LOCAL INFILE '//tsn.tno.nl/Data/Users/rietjv/Home/Documents/Database/extra/compoundList.txt' INTO TABLE tCompound (casNumber, name, abbreviation);