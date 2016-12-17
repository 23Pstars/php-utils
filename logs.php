<?php
/**
 * LRsoft Corp.
 * http://lrsoft.co.id
 *
 * Author : Zaf
 *
 * Script ini untuk melakukan analisa IP terhadap file log di server
 * seberapa sering sebuah alamat IP melakukan request,
 * untuk menentukan apakah perlu di block atau tidak.
 */

set_time_limit(0);

global $min_number_to_indicate;

//$log_path = '/media/WORK/localhost/lrsoft/logs/';
$log_path = '/usr/local/apps/apache/logs/';
$files_per_page = 10;
$filenames = array();
$results = array();

$status = true;

$min_number_to_indicate = isset( $_REQUEST[ 'min_number_to_indicate' ] ) ? $_REQUEST[ 'min_number_to_indicate' ] : 15;
$start = isset( $_REQUEST[ 'filenames_start' ] ) ? $_REQUEST[ 'filenames_start' ] : 1;
$offset = isset( $_REQUEST[ 'filenames_offset' ] ) ? $_REQUEST[ 'filenames_offset' ] : $files_per_page;

if( $dir_handle = opendir( $log_path ) ) {

    while ( ( $file = readdir( $dir_handle ) ) != false )
        if( '.' != $file && '..' != $file && '.log' == substr( $file, -4, 4 ) )
            $filenames[] = $log_path . $file;

    asort( $filenames );
    $total_files = count( $filenames );
    $filenames = array_slice( $filenames, $start-1, $offset );

    echo 'list of files :<br/>';
    foreach( $filenames as $k => $filename )
        echo ( $start + $k ) . '&nbsp;&nbsp;&mdash;&nbsp;' . $filename . '<br/>';
    echo '<br/>';
    echo 'Summary : ' . count( $filenames ) . ' (offset) of Total : ' . $total_files . '<br/><br/>';



    if( isset( $_REQUEST[ 'find_indicate_ip' ] ) ) {

        foreach( $filenames as $filename ) {
            $handle = fopen( $filename, 'r' ) or die( 'File not found!' );
            if ( $handle ) {
                while( !feof( $handle ) ) {
                    $buffer = fgets( $handle );
                    list( $ip,,,,,,,,$request_status,,$refer,$user_agent ) = explode( ' ', $buffer );
                    if( '404' == $request_status && '"-"' == trim( $user_agent ) ) {
                        if( array_key_exists( $ip, $results ) ) $results[ $ip ] = (int)$results[ $ip ] + 1;
                        else $results[ $ip ] = 1;
                        //echo $ip . ' : ' . $request_status . ' : ' . $refer . ' : ' . $user_agent . '<br/>';
                        //echo '<pre>'; print_r( explode( ' ', $buffer ) ); echo '</pre>';
                    }
                }
                fclose($handle);
            }
        }
        $results = array_filter( $results, function( $el ){ global $min_number_to_indicate; return $el >= $min_number_to_indicate ? $el : false; } );
        asort( $results );

        echo 'list of indicate IP\'s :<br/>';
        foreach( $results as $ip => $times )
            echo '&nbsp;&nbsp;&mdash;&nbsp;' . $ip . '&nbsp;&nbsp;-->&nbsp;&nbsp;' . $times . ' times<br/>';
        echo 'Total : ' . count( $results );

    } else if( isset( $_REQUEST[ 'clean_up_logs' ] ) ) {

        foreach( $filenames as $filename )
            file_put_contents( $filename, '' ) || $status = false;

        echo ( $status ? 'Success' : 'Error' ) . ' to clean log files.';

    } else if( isset( $_REQUEST[ 'clean_up_errors' ] ) ) {

        foreach( $filenames as $filename )
            file_put_contents( str_replace( '.log', '.err', $filename ), '' ) || $status = false;

        echo ( $status ? 'Success' : 'Error' ) . ' to clean error log files.';

    }

    ?>
    <br/><br/>
    <form method="get">
        <label for="filenames_start">start</label>
        <input type="text" id="filenames_start" name="filenames_start" value="<?php echo $start; ?>" style="width: 20px;">
        &nbsp;&mdash;&nbsp;
        <label for="filenames_offset">offset</label>
        <input type="text" id="filenames_offset" name="filenames_offset" value="<?php echo $offset; ?>" style="width: 20px;">
        <br/><br/>
        <label for="min_number_to_indicate">min number to indicate</label>
        <input type="text" id="min_number_to_indicate" name="min_number_to_indicate" value="<?php echo $min_number_to_indicate; ?>" style="width: 20px;">
        <br/><br/>
        <input type="submit" name="find_indicate_ip" value="find_indicate_ip">
        <input type="submit" name="clean_up_logs" value="clean_up_logs">
        <input type="submit" name="clean_up_errors" value="clean_up_errors">
    </form>
    <?php

} else echo 'I can not read this path : ' . $log_path;
