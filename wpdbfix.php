<?php

define( 'WP_HOST', 'localhost' );
define( 'WP_NAME', 'test' );
define( 'WP_USERNAME', 'user' );
define( 'WP_PASSWORD', 'pass' );
define( 'WP_TABLE_POSTS', 'wp_posts' );

define( 'KEYWORD', 'viagra' );

define( 'REGEX', '/<div style(.*?)>(.*?)(viagra|cialis|doxycycline|pharmacy)(.*?)<\/div>/' );
// define( 'REGEX', '/<div style(.*?)>(.*?)(viagra|cialis|doxycycline|pharmacy)(.*?)\n/' );
// define( 'REGEX', '/<a.*?(viagra|cialis|doxycycline|pharmacy)<\/a>/' );
// define( 'REGEX', '/(cialis|viagra).*?\./' );

class DBFix {

  private $db_conn = null;
  private $db_select = null;

  function __construct(){
    $this->_connect();
  }

  private function _connect( $host = WP_HOST, $name = WP_NAME, $user = WP_USERNAME, $pass = WP_PASSWORD, $new_link = false ) {
    if( !$this->db_conn = mysql_connect( $host, $user, $pass, $new_link ) )
      if( !$this->db_conn = mysql_connect( WP_HOST, WP_USERNAME, WP_PASSWORD, $new_link ) )
        die( 'Ndeq ne bau konek kadu User : ' . $user );

    if( !$this->db_select = mysql_select_db( $name, $this->db_conn ) )
      if( !$this->db_select = mysql_select_db( WP_NAME, $this->db_conn ) )
        die( 'Ndek ne bau konek jok DB : ' . $name );
  }

  function _search() {
    $results = array();
    $query = "SELECT `ID`, `post_content` FROM " . WP_TABLE_POSTS . " WHERE (CONVERT(`ID` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_author` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_date` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_date_gmt` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_content` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_title` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_excerpt` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_status` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`comment_status` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`ping_status` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_password` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_name` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`to_ping` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`pinged` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_modified` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_modified_gmt` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_content_filtered` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_parent` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`guid` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`menu_order` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_type` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`post_mime_type` USING utf8) LIKE '%" . KEYWORD . "%' OR CONVERT(`comment_count` USING utf8) LIKE '%" . KEYWORD . "%')";
    $resource = mysql_query( $query );
    while( $row = mysql_fetch_assoc( $resource ) )
      $results[] = $row;
    return $results;
  }

  function _init() {
    $search_results = $this->_search();
    foreach( $search_results as $post ) {
      // echo  preg_replace( REGEX, '', preg_replace('/\s\s+/', ' ', $post[ 'post_content' ])) . PHP_EOL . PHP_EOL;
      echo 'ID: ' . $post[ 'ID' ] . ' : ' . ( $this->_update( $post[ 'ID' ], preg_replace( REGEX, '', preg_replace('/\s\s+/', ' ', $post[ 'post_content' ]) ) ) ? 'success' : 'fails' ) . PHP_EOL;
    }
  }

  function _update( $post_id, $post_content ) {
    $query = "UPDATE " . WP_TABLE_POSTS . " SET `post_content` = '" . mysql_real_escape_string( $post_content ) . "' WHERE `ID` = " . $post_id;
    return mysql_query( $query );
  }

}

$obj = new DBFix();
$obj->_init();
