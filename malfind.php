<?php
/**
 * LRsoft Corp.
 * http://lrsoft.co.id
 *
 * Author : Zaf
 *
 * Script ini untuk melakukan scanning terhadap semua file PHP
 * biasanya untuk web yang pernah terkena injection attack.
 * berdasarkan keyword ataupun regex pattern
 */

$domain = isset( $_REQUEST[ 'domain' ] ) ? htmlspecialchars_decode( $_REQUEST[ 'domain' ] ) : false;
$find = isset( $_REQUEST[ 'find' ] ) ? htmlspecialchars_decode( $_REQUEST[ 'find' ] ) : false;
$pattern = isset( $_REQUEST[ 'pattern' ] ) ? htmlspecialchars_decode( $_REQUEST[ 'pattern' ] ) : '';
$replace = isset( $_REQUEST[ 'replace' ] ) ? htmlspecialchars_decode( $_REQUEST[ 'replace' ] ) : '';
$unlink = isset( $_REQUEST[ 'unlink' ] ) ? $_REQUEST[ 'unlink' ] : array();

if( isset( $_REQUEST[ 'submit' ] ) ) {

    if( !$domain ) exit( 'Please provide a domain!' );

    $iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $domain ), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD );
    $file_lists = array();
    $result = array();

    $total = 0;

    foreach ( $iterator as $path )
        if( !$path->isDir() && '.php' == substr( $path->__toString(), -4, 4 ) && './' . basename( __FILE__ ) != $path->__toString() )
            $file_lists[] = $path->__toString();

    foreach( $file_lists as $file ) {
        $content = file_get_contents( $file );
        if( isset( $_REQUEST[ 'regex' ] ) && 'yes' == $_REQUEST[ 'regex' ] ) {
			if( preg_match( htmlspecialchars_decode( $pattern ), $content, $match ) ) {
                $match_clean = htmlspecialchars( $match[0] );
                $total += 1;
                echo $file . ' ';
                if( isset( $_REQUEST[ 'unlink_radio' ] ) && 'yes' == $_REQUEST[ 'unlink_radio' ] )
					echo unlink( $file ) ? 'removed.' : 'error.';
                elseif( isset( $_REQUEST[ 'heal' ] ) && 'yes' == $_REQUEST[ 'heal' ] )
                    echo file_put_contents( $file, str_replace( $match[0], $replace, $content ) ) ? 'healed.' : 'error.';
                echo '<br/>&nbsp;&nbsp;&mdash;&nbsp;&nbsp;found : ' . $match_clean . '<br/>';
                in_array( $match_clean, $result ) || $result[] = $match_clean;
            }
		} else {
			if( strpos( $content, $find ) !== false ) {
				$total += 1;
				echo $file . ' ';
				if( isset( $_REQUEST[ 'unlink_radio' ] ) && 'yes' == $_REQUEST[ 'unlink_radio' ] )
					echo unlink( $file ) ? 'removed.' : 'error.';
                elseif( isset( $_REQUEST[ 'heal' ] ) && 'yes' == $_REQUEST[ 'heal' ] )
                    echo file_put_contents( $file, str_replace( $match[0], $replace, $content ) ) ? 'healed.' : 'error.';
				echo '<br/>';
			}
		}
    }

    echo '<br/><h1>Result : </h1>';
    echo '<pre>'; print_r( $result ); echo '</pre>';
    echo '<br/><h1>Total : ' . $total . '</h1>';

    echo '<br/><br/><br/>';
    echo '<h1>Unlink</h1>';
    foreach( $unlink as $ul ) {
		if( !empty( $ul ) ) {
			$ul = $domain . $ul;
			if( file_exists( $ul ) ) {
				if( isset( $_REQUEST[ 'unlink_radio' ] ) && 'yes' == $_REQUEST[ 'unlink_radio' ] ) {
					echo $ul . ( unlink($ul) ? ' removed.<br/>' : ' failed to remove.<br/>' );
				} else {
					echo $ul . ' exist.<br/>';
				}
			} else {
				echo $ul . ' NOT FOUND.<br/>';
			}
		}
	}
	echo '<br/><br/><br/>';

} ?>

<form method="post">
    <label>Domain <input type="text" name="domain" value="<?php echo htmlspecialchars( $domain ); ?>" style="width: 800px;"/></label>
    <br/><br/>
    <label>Keyword <input type="text" name="find" value="<?php echo htmlspecialchars( $find ); ?>" style="width: 800px;"/></label>
    <br/><br/>
    Regex ?
    <label for="regex_yes"><input type="radio" name="regex" id="regex_yes" value="yes" /> Yes</label>
    <label for="regex_no"><input type="radio" name="regex" id="regex_no" value="no" checked /> No</label>
    <br/><br/>
    <label>Pattern <input type="text" name="pattern" value="<?php echo htmlspecialchars( $pattern ); ?>" style="width: 800px;"/></label>
    <br/>Usually :<br/>
    <ul>
        <li><?php echo htmlspecialchars( '/^(\s*\n)(<\?php)/' ); ?></li>
        <li><?php echo htmlspecialchars( '/(eval\()(.*)(base64_decode\()(.*)(\)\);)/' ); ?></li>
        <li><?php echo htmlspecialchars( '/(\<script)(.*)(src=")(.*)collect.js(.*)(\/script\>)/' ); ?></li>
        <li><?php echo htmlspecialchars( '/(eval)(.*)(\()(.*)(\)(.*);)/' ); ?></li>
    </ul>
    <br/><br/>
    Heal ?
    <label for="heal_yes"><input type="radio" name="heal" id="heal_yes" value="yes" /> Yes</label>
    <label for="heal_no"><input type="radio" name="heal" id="heal_no" value="no" checked /> No</label>
    <br/><br/>
    <label>Replace <input type="text" name="replace" value="<?php echo htmlspecialchars( $replace ); ?>" style="width: 800px;"/></label>
    <br/><br/>
    Unlink ?
    <label for="unlink_yes"><input type="radio" name="unlink_radio" id="unlink_yes" value="yes" /> Yes</label>
    <label for="unlink_no"><input type="radio" name="unlink_radio" id="unlink_no" value="no" checked /> No</label>
    <br/><br/>
    <?php for( $i=0; $i<20; $i++ ) : ?>
		<input type="text" name="unlink[]" value="<?php echo isset( $_REQUEST[ 'unlink' ][$i] ) ? $_REQUEST[ 'unlink' ][$i] : ''; ?>" /><br/>
    <?php endfor; ?>
    <br/><br/>
    <input type="submit" name="submit" name="submit" />
</form>
