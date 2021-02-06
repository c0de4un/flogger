<?php

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
// CLASS
// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

/**
 * @brief
 * File-based logger utility
 * 
 * @version 0.1.4
 */
final class FLogger
{

    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
	
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                        CONSTANTS
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /** @var String root-dir */
    private $root_dir;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                          FIELDS
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    
    /** @var FLogger */
    private static $instance = null;

    /** @var Array[String] cached file names. */
    private $file_names = [];

    /** @var Array[File] */
    private $files = [];

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                        CONSTRUCTOR
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    private function __constructor()
    {
        $this->root_dir = getcwd();

        register_shutdown_function( [$this, 'handleShutdown'] );
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                     GETTERS & SETTERS
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    
    /**
     * @brief
     * 
     * @param Boolean $alloc = true
     * @return FLogger||NULL
    */
    public static function getInstance( bool $alloc = true )
    {
        if ( $alloc && empty(self::$instance) ) {
            self::$instance = new FLogger();
        }

        return self::$instance;
    }

    /**
     * Set new Root-dir
     * 
     * @param String $dir
    */
    public function setRootDir( string $dir )
    {
        if ( $dir[strlen($dir) - 1] !== '/' ) {
            $dir .= '/';
        }
        
        $this->root_dir = $dir;
    }

    /**
     * @brief
     * Returns appropriate File instance for context & dir
     * 
     * @param String $context
     * @param String $dir
     * 
     * @return File
    */
    private function getFile( $context, $dir )
    {
        if ( empty($this->files[$context]) ) {
            $this->verifyDirs( $dir );
            $file_name = $this->root_dir . $dir . '/' . $this->getLogFile_Name( $context ) . '.log';

            return $this->files[$context] = fopen( $file_name, 'a' );
        }
        
        return $this->files[$context];
    }

    /**
     * @brief
     * Build date-time mark for file-name
     * 
     * @return String
    */
    private function getDateMark()
    {
        return date('Y_m_d', time() );
    }

    /**
     * @brief
     * Build log-file name
     * 
     * @param String $context
     * 
     * @return String
    */
    private function getLogFile_Name( $context )
    {
        if ( empty($this->file_names[$context]) ) {
            $output = $this->file_names[$context] = $context.'_'.$this->getDateMark();
        } else {
            $output = $this->file_names[$context];
        }
        
        return $output;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                       METHODS.PUBLIC
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * @brief
     * Called when script ends
    */
    public function handleShutdown(): void
    {
        $instance = self::getInstance( false );
        $instance->close();
    }

    public static function info( $msg, $context = 'core', $dir = 'log' )
    {
        self::print( "GOOD: {$msg}", $context, $dir );
    }

    public static function verbose( $msg, $context = 'core', $dir = 'log' )
    {
        self::print( "VERBOSE: .{$msg}", $context, $dir );
    }

    public static function warning( $msg, $context = 'core', $dir = 'log' )
    {
        self::print( "WARNING: {$msg}", $context, $dir );
    }

    public static function error( $msg, $context = 'core', $dir = 'log' )
    {
        self::print( "FATAL_ERROR: {$msg}", $context, $dir );
    }

    /**
     * @brief
     * Append output for file
     * 
     * @param String $msg
     * @param String $context = 'core'
     * @param String $dir = 'log'
    */
    private static function print( $msg, $context = 'core', $dir = 'log' ): void
    {
        $instance = self::getInstance();
        $file = $instance->getFile( $context, $dir );
        $dt_mark = date( 'Y-m-d H:i:s' );
        fwrite( $file, $dt_mark.PHP_EOL.$msg.PHP_EOL );
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    //                      METHODS.PRIVATE
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * @brief
     * Build missing dirs in the path
     * 
     * @param String $path
    */
    private function verifyDirs( $path ): void
    {
        if ( !file_exists($this->root_dir . $path) ) {
            try {
                mkdir( $this->root_dir . $path, 0777, true );
            } catch( Exception $exception ) {
                /* - void - */
            } finally { /* - void - */ }
        }
    }

    /**
     * @brief
     * Close/flush output files
    */
    private function close(): void
    {
        if ( !empty($this->files) ) {
            foreach( $this->files as &$file )
            {
                fclose( $file );
            }
        }
    }

    // = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

};
class_alias( FLogger::class, 'MyLogger' );

// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
