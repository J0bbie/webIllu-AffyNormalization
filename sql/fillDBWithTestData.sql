#Studytypes
INSERT INTO `normdb`.`tStudyType` (`name`) VALUES ('Single Dose Toxicity');
INSERT INTO `normdb`.`tStudyType` (`name`) VALUES ('Genotoxicity');

#Arrayplatform
INSERT INTO `normdb`.`tArrayPlatform` (`name`, annoType, arrayType, platformType) VALUES ('Illumina BeadChip - HumanHT-12_V4_0_R2_15002873_B', 'HumanHT-12_V4_0_R2_15002873_B', 'HumanHT-12', 'illu');
INSERT INTO `normdb`.`tArrayPlatform` (`name`, annoType, arrayType, platformType) VALUES ('Illumina BeadChip - Custom Annotation', 'Custom Annotation', 'Custom Annotation', 'illu');
INSERT INTO `normdb`.`tArrayPlatform` (`name`, annoType, arrayType, platformType) VALUES ('Affymetrix - Human Genome U133 Plus 2.0', 'hgu133plus', 'U133', 'affy');
INSERT INTO `normdb`.`tArrayPlatform` (`name`, annoType, arrayType, platformType) VALUES ('Affymetrix - Custom Annotation', 'Custom Annotation', 'Custom Annotation', 'affy');

#Assay types
INSERT INTO `normdb`.`tDomains` (`name`) VALUES ('Liver toxicity');
INSERT INTO `normdb`.`tDomains` (`name`) VALUES ('Kidney toxicity');
INSERT INTO `normdb`.`tDomains` (`name`) VALUES ('Developmental toxicity');

#Directories used to store files
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('raw', 'Folder to hold all the raw data.');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('sampleAnnotation', 'Folder to hold the annotation of the samples. ');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('expressionData/raw', 'Folder to hold all the expression data. (Provided by SXS)');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('expressionData/normed', 'Folder to hold all the normalized expression data.');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('statistics/', 'Folder to hold all the results from QC of raw data.');
INSERT INTO `normdb`.`tDirectory` (`folderName`, `description`) VALUES ('Unknown', 'Could not determine the filetype!');

#Type of files
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
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Raw Illumina Data LumiBatch Object', 'Raw Illumina data from this pipeline in a LumiBatch R Object.', 4, 'rawData.Rdata');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('customAnnotationFile', 'File containing the custom layout of the array.', 3, 'customAnnotation');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('affyCelFile', 'File containing the gene expressions from an Affymetrix run in .CEL format.', 3, '.cel');

# Output of illu normalization
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Illumina Data', 'Normalized Illumina data from this pipeline.', 4, 'normData_lumi');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Illumina Data LumiBatch Object', 'Normalized Illumina data from this pipeline in a LumiBatch R Object.', 4, 'normData.Rdata');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Illumina Data with Entrez', 'Normalized Illumina data from this pipeline merged with entrez genes.', 4, 'normData_lumi_merged');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Illumina Data Filtered', 'Normalized Illumina data from this pipeline with probes/genes with a low expression filtered.', 4, 'normData_Filtered');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Summary raw data', 'Summary of raw data statistics.', 4, 'summary_rawData');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Summary norm data', 'Summary of norm data statistics.', 4, 'summary_normData');

# Illu raw plots
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Boxplot of amplitudes (raw data)', 'Boxplot of amplitudes', 5, 'RAW_boxplot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Covariance plot (raw data)', 'Plot density for coefficient of varience for intensities', 5, 'RAW_cv_plot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Density histogram (raw data)', 'Plot density histogram for intensities', 5, 'RAW_density');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Data array correlation (raw data)', 'Correlation plot for array correlation', 5, 'RAW_dataArrayCorrelation');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Cluster plot (raw data)', 'Cluster plot based on defined group', 5, 'RAW_dataCluster');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('PCA (raw data)', 'Plot density for coefficient of varience for intensities', 5, 'RAW_dataPCA');

# Illu norm plots
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Boxplot of amplitudes (norm data)', 'Boxplot of amplitudes', 5, 'NORM_boxplot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Covariance plot (norm data)', 'Plot density for coefficient of varience for intensities', 5, 'NORM_cv_plot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Density histogram (norm data)', 'Plot density histogram for intensities', 5, 'NORM_density');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Data array correlation (norm data)', 'Correlation plot for array correlation', 5, 'NORM_dataArrayCorrelation');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Cluster plot (norm data)', 'Cluster plot based on defined group', 5, 'NORM_dataCluster');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('PCA (norm data)', 'Plot density for coefficient of varience for intensities', 5, 'NORM_dataPCA');

# Output of affy normalization
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Affymetrix Data', 'Normalized Affymetrix data from this pipeline.', 4, 'NormData');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Log pipeline', 'Log of pipeline', 4, 'log_pipeline');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Normalized Affymetrix Data LumiBatch Object', 'Normalized Affymetrix data from this pipeline in a LumiBatch R Object.', 4, 'normData.Rdata');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Raw Affymetrix Data LumiBatch Object', 'Raw Affymetrix data from this pipeline in a LumiBatch R Object.', 4, 'rawData.Rdata');

# Affy total report
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Report normalization and QC Affymetrix pipeline', 'Report containing the plots of the Affymetrix normalization pipeline', 5, 'REPORT_Affy');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Cover report page 1', 'Front cover of the report', 5, 'Cover_1');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Cover report page 2', 'Second page of the report', 5, 'Cover_2');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Description normalization', 'Description of the experiment', 5, 'Description_Affy');

# Affy raw plots
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot raw sample prep controls', 'Plot of the raw sample prep controls from the affy pipeline.', 5, 'RawDataSamplePrepControl');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot raw beta-actin & GAPDH 3/5 ratio', 'Plot of the raw beta-actin 3/5 ratio from the affy pipeline.', 5, 'RawData53ratioPlot_beta-actin');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot raw beta-actin & GAPDH 3/5 ratio', 'Plot of the raw GAPDH 3/5 ratio from the affy pipeline.', 5, 'RawData53ratioPlot_GAPDH');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot raw RNA degradation plot', 'Plot of the raw RNA degradation bias from the affy pipeline.', 5, 'RawDataRNAdegradation');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('BoxPlot all controls', 'BoxPlot of all controls from the affy pipeline.', 5, 'RawDataAFFX1ControlsBoxplot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot raw spike-in hybridization controls plot', 'Plots of the raw spike-in hybridization controls from the affy pipeline.', 5, 'RawDataAFFXControlsProfiles');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Data array correlation Affymetrix (raw data)', 'Correlation plot for array correlation', 5, 'RawDataArrayCorrelation');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot of background intensities (raw data)', 'Plot of background intensities', 5, 'RawDataBackground');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Boxplot of amplitudes Affymetrix (raw data)', 'Boxplot of raw amplitudes', 5, 'RawDataBoxplot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Cluster plot Affymetrix (raw data)', 'Cluster plot based on defined group', 5, 'RawDataCluster');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('MA plot Affymetrix (raw data)', 'MA plot of raw arrays', 5, 'RawDataMAplot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('NUSE plot Affymetrix (raw data)', 'NUSE plot of raw arrays', 5, 'RawDataNUSE');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('PCA plot Affymetrix (raw data)', 'PCA plot of raw arrays', 5, 'RawDataPCAanalysis');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot Present calls Affymetrix (raw data)', 'Plot of the present calls', 5, 'RawDataPercentPresent');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot of distributions of pos/neg calls Affymetrix (raw data)', 'Distribution plot of the POS/NEG calls', 5, 'RawDataPosNegDistribution');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot of positions of pos/neg calls Affymetrix (raw data)', 'Position plot of the POS/NEG calls', 5, 'RawDataPosNegPositions');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot of data reference layout Affymetrix (raw data)', 'Plot of data reference layout Affymetrix', 5, 'RawDataReferenceArrayLayout');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('RLE plot Affymetrix (raw data)', 'RLE plot of raw arrays', 5, 'RawDataRLE');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('RLE plot Affymetrix (raw data)', 'RLE plot of raw arrays', 5, 'RawDataRLE');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('BoxPlot raw spike-in hybridization controls plot', 'BoxPlot of spike-in hybridization controls from the affy pipeline.', 5, 'RawDataSpikeinHybridControl');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Plot scale factors', 'Plot of the scale factors', 5, 'RawDataScaleFactors');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Density histogram Affymetrix (raw data)', 'Plot density histogram for raw intensities', 5, 'RawDensityHistogram');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('PMA Table', 'Present/Margin/Absent calls table', 5, 'PMAtable');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('QC Table', 'Table of raw QC calls', 5, 'QCtable');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('2D virtual PLM image', '2D virtual PLM image', 5, 'RawData2DVirtualImage');

# Affy norm plots
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Data array correlation Affymetrix (norm data)', 'Correlation plot for array correlation', 5, 'NormDataArrayCorrelation');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Boxplot of amplitudes Affymetrix (norm data)', 'Boxplot of amplitudes', 5, 'NormDataBoxplot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Cluster plot Affymetrix (norm data)', 'Cluster plot based on defined group', 5, 'NormDataCluster');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('MA plot Affymetrix (norm data)', 'MA plot of normalized arrays', 5, 'NormDataMAplot');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('PCA plot Affymetrix (norm data)', 'PCA plot of normalized arrays', 5, 'NormDataPCAanalysis');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Density histogram Affymetrix (norm data)', 'Plot density histogram for intensities', 5, 'NormDensityHistogram');

# Used for defining the subset on which statistics are run
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('StatSubsetFile', 'File containing the sampleNames used in the statistics if a subset was used.', 5, 'statSubsetFile');

#Raw data illumina merged
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Raw Illumina Data', 'Raw Illumina merged data from this pipeline.', 4, '_rawData');

#Description files
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Description File Norm', 'Description file for normalization.', 4, 'descriptionFile');
INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Description File Stat', 'Description file for statistics only.', 5, 'descriptionFile');

INSERT INTO `normdb`.`tFileType` (`name`, `description`, `idDirectory`, `searchOn`) VALUES ('Log File Stat', 'Log file for statistics only.', 5, '_log');

#Sample types
INSERT INTO `normdb`.`tSampleType` (`name`, `description`) VALUES ('posControl', 'Positive control');
INSERT INTO `normdb`.`tSampleType` (`name`, `description`) VALUES ('negControl', 'Negative control');
INSERT INTO `normdb`.`tSampleType` (`name`, `description`) VALUES ('sample', 'Study sample');

#Species
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Human', 'Homo Sapiens');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('A. gambia', 'Anopheles gambiae');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Arabidopsis', 'Arabidopsis thaliana');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Cow', 'Bos taurus');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('C. elegans', 'Caenorhabditis elegans');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Dog', 'Canis lupus familiaris');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Zebrafish', 'Danio rerio');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Fruit fly', 'Drosophilia melanogaster');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Chicken', 'Gallus gallus');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Rhesus macaque', 'Macaca mulatta');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Mouse', 'Mus musculus');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Asian rice', 'Oryza sativa');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Rat', 'Rattus norvegicus');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Yeast', 'Saccharomyces cerevisiea');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Fission yeast', 'Schizosaccharomyces pombe');
INSERT INTO `normdb`.`tSpecies` (`genericName`, `latinName`) VALUES ('Wild boar', 'Sus scrofa');

#Assay types
INSERT INTO `normdb`.`tAssayType` (`name`) VALUES ('In vivo');
INSERT INTO `normdb`.`tAssayType` (`name`) VALUES ('Ex vivo');
INSERT INTO `normdb`.`tAssayType` (`name`) VALUES ('In vitro');

#Datatypes
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
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('chemicalAbbreviation', 'Abbr. given to a chemical compound');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('description', 'Description');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('studyNoAel', 'studyNoAel');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('noAel_1', 'noAel_1');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('noAel_2', 'noAel_2');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('noAel_3', 'noAel_3');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('minNoel', 'Minimum range of noAel');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('maxNoel', 'Maximum range of noAel');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('methodSampling', 'Method of sampling');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('extraIdentifier1', 'Extra identifier');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('extraIdentifier2', 'Extra identifier');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('extraIdentifier3', 'Extra identifier');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('researchPartner', 'Name of the research partner where samples originate from');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('ic10', 'Value where 10% percent of cells have died due to treatment');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('columnPurification', 'Method of column purification');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('nucAcidConcentration_ng/ul', 'Concentration of nucleic acid in ng/ul');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('A260', 'Nucleic Acid Measurement of DNA/RNA');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('A280', 'Nucleic Acid Measurement of DNA/RNA');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('A260/A280', 'Fraction A260/A280');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('A260/A230', 'Fraction A260/A230');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('measureType', 'What is measured, RNA/DNA etc');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('volume_ml', 'Volume of measurement in ml');
INSERT INTO `normdb`.`tDataType` (`name`, `description`) VALUES ('totalAmountRNA', 'Total amount of RNA');

#Read in a tab-delimited file with compounds + cas + name
LOAD DATA LOCAL INFILE '/home/jobbie/Desktop/TNO/Project/compoundList.txt' INTO TABLE tCompound (casNumber, name, abbreviation);
