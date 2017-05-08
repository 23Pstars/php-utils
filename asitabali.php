<?php
/**
 * LRsoft Corp.
 * http://lrsoft.co.id
 *
 * Author : Zaf
 *
 * Crawl anggota ASITA Bali yang ada di web
 */

$_csv = fopen('ASITA_BALI.csv','w');
fputcsv($_csv,array('Nama Usaha','Alamat','Telp','Fax','Email','Owner'));

foreach(range(1,27) as $_page){

  $_data = file_get_contents('http://www.asitabali.org/asita_member.php?&act=listuser&letter=All&sortby=id&page='.$_page);

  preg_match_all('/<table width=\"280\"[.\s\S]+?<\/table>/', $_data, $_agents);

  unset($_agents[0][0]);

  foreach( $_agents[0] as $_agent ){

    preg_match_all('/<span[.\s\S]+?<\/span>/', $_agent, $_infos );

    unset($_infos[0][1]);

    array_walk($_infos[0],function(&$item){
      $item = strip_tags($item);
    });

    fputcsv($_csv,$_infos[0]);

  }

}