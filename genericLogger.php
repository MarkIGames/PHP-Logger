<?php

	/**
	 * genericLogger.php
	 *
	 * This file handles log writting properties and functions for any application.
	 *
	 * @category   Generic - General
	 * @since      Created: February 20th, 2013
	 * @link       class/genericLogger.php
	 * @author     Mark Williams
	 * @uses       N/A
	 *
	 * genericLogger.php
	 *
	 * This class has a number of functions in it that can be used to write log
	 * data out to the database. The primary function of this application is to
	 * allow a developer to specify a directory and a log file and a message, 
	 * and this class will write the message out to the log file. To avoid 
	 * creating logs that will become unreadable, the class first checks if the
	 * log file exists and reports back it's existing size, and then determines
	 * if the log file needs to be archived off automatically or not.
	 *
	 * Updated 7/24/2013
	 * 
	 * Added support to the writeToLog to use a default value for Filesize if 
	 * one is not given.
	 */
	
	class genericLogger {

	    /** setupCustomPHPErrorFile( $fileName, $filePath )
	     * 
	     * This function will configure your PHP Errors to write out to their
	     * own PHP Error file. This can keep from junking up production log
	     * files if you're running development code on production. 
	     * 
	     * This method requires a path and a name for the file you want to 
	     * write to. The IIS user must have write permissions to this path.
	     * 
	     * This should be a system path, not a relative or HTTP path for 
	     * Windows systems in order for the PHP error_log parameter to 
	     * function.
	     * 
	     * @param string $filePath
	     * @param string $fileName
	     */
	    public static function setupCustomPHPErrorFile( $fileName, $filePath ) {
	        // Setup the new location string
	        $newLocation =  $filePath . '\\' . $fileName;

	        // Set the Ini setting itself with the new location and filename
	        ini_set( 'error_log', $newLocation );
	    }
	    
		/** writeToLog( $logMsg, $filePath, $fileName, $fileSize )
		 * 
		 * This file will take a message, name, path and size and check the log
		 * file for the proper size, and if it is not within the proper size
		 * archive it off with a time stamp, and then write the message in. Or
		 * if it's inside the required log file size, it will write right away
		 * without needing to archive.
		 * 
		 * This should prevent log files from ever getting to large to be 
		 * written too.
		 * 
		 * @param string  $logMsg
		 * @param string  $filePath
		 * @param string  $fileName
		 * @param integer $filesize (In Bytes)
		 */
	    public static function writeToLog( $logMsg, $filePath, $fileName, $fileSize = 52428800 ) {
	        // Get the logsize of the file we want to write to
	        $logSize = genericLogger::getLogSize( $fileName, $filePath );
	         
	        // If the logsize is larger then our maximum logsize...
	        if($logSize > $fileSize) {
	            // Archive the log file
	            genericLogger::archiveLogFile( $fileName, $filePath );
	        }
	         
	        // Otherwise, write in our log message!
	        genericLogger::commitToLogs( $logMsg, $fileName, $filePath );
	    }

	    /** checkDefaultPHPLog( $fileName, $filePath, $fileSize )
	     * 
	     * This function will check the PHP log file size, and archive it off
	     * if necessary. 
	     * 
	     * @param string  $fileName
	     * @param string  $filePath
	     * @param integer $fileSize (In Bytes)
	     */
	    public static function checkAndArchiveLog($fileName, $filePath, $fileSize) {
	        // Get the logsize of the file we want to write to
	        $logSize = genericLogger::getLogSize( $fileName, $filePath );
	        
	        // If the logsize is larger then our maximum logsize...
	        if($logSize > $fileSize) {
	            // Archive the log file
	            genericLogger::archiveLogFile( $fileName, $filePath );
	        }
	    }	    
	    
	    /** getLogSize( $fileName, $filePath )
	     *
	     * This function takes a filename and looks in the log directory for
	     * the file. If the file exists, it checks and returns the file size
	     * otherwise it returns 0.
		 *
	     * The return value is in bytes.
	     *
	     * @param string   $fileName
	     * @param string   $filePath
	     * @return integer $logSize (In Bytes)
	     */
	    private static function getLogSize( $fileName, $filePath ) {
	        // Populate our return value so we always pass something out
	        $logSize = 0;
	         
	        // Check to see if the file we want to check already exists
	        if(file_exists( $filePath . '\\' . $fileName )) {
	            // If the file exists, get the logsize
	            $logSize = filesize( $filePath . '\\' . $fileName );
	        }
	    
	        // Always return the logsize
	        return $logSize;
	    }
	     
	    /** archiveLogFile( $fileName, $filePath )
	     *
	     * This function takes a log file name and path and looks in the log
	     * directory for it. If the file is there, it will rename the file with
	     * a seconds based timestamp.
	     *
	     * @param string $fileName
	     * @param string $filePath
	     */
	    private static function archiveLogFile($fileName, $filePath) {
	        // Save the old name
	        $oldName  = $fileName;
	         
	        // Setup the new name
	        $newName  = $fileName . time();
	         
	        // Rename the file
	        rename( $filePath . '\\' . $oldName, $filePath . '\\' . $newName );
	    }
	   
	    /** commitToLogs( $logMsg, $fileName, $filePath )
	     *
	     * This function will actually take a message and a filename and write
	     * it to the log file. This happens last in the process after the size
	     * has been checked and the file has been archived (if needed).
	     *
	     * @param string $logMsg
	     * @param string $fileName
	     * @param string $filePath
	     */
	    private static function commitToLogs( $logMsg, $fileName, $filePath ) {
	        // Set up the File path to write to with the file name appended
	        $fileLocation = $filePath . '\\' . $fileName;

	        // Open the file with PHP
	        $handle = @fopen( $fileLocation, 'a+' );
	         
	        // Write out to the file
	        fwrite( $handle, date('YmdHis') . " $logMsg\r\n" );
	         
	        // Close the file
	        @fclose( $handle );
	    }	    
	    
	}
