# Author:                      Job van Riet
# Date of  creation:           16-4-14
# Date of modification:        16-4-14
# Version:                     1.0
# Modifications:               Original version
# Known bugs:                  None known
# Function:                    This script contains the configuration setting of the Affymetrix and Illumina pipeline


#####################################################################################################
#                                       Database settings                                           #
#####################################################################################################

# Configure the setting to correspond to your MySQL configuration

# User of MySQL with CREATE/UPDATE permission into the correct schema
configDbUser = "<userName>"     # normUser
configDbUserPass = "<userPass>" # normPass231
configDbSchema = "<dbSchema>"   # normDB
configDbHost = "<serverName>"   # 127.0.0.1

#####################################################################################################
#                                       Folder configuration                                        #
#####################################################################################################

# Set the main folder of the application, this is the folder which contains the GUI/R/logic/ etc. folders
configMainFolder = "C:/Users/rietjv/AppData/Local/My Local Documents/Github/webIlluminaNormalization/diamondsNorm/"       # /var/www/normDiamonds/


