<?php

$ls = shell_exec("/bin/ls img/userPic_*");
$ls = explode('
',$ls);

foreach ($ls as $tmp_file) {
    $file = pathinfo($tmp_file);
	
	$filename_o = $file['filename'].'.png';
	$filename_o = str_replace('userPic_','',$filename_o);
	$filename_ot = 'rounded/circle_'.$filename_o;



	$convert_gm = '/usr/local/bin/gm';
	$convert_magick = '/usr/local/bin/convert';
	$post_convert = '/usr/local/bin/pngquant';

    $size = getimagesize($tmp_file);
	
	exec("$convert_gm convert -size ".$size[0]."x".$size[1]." $tmp_file -thumbnail 266x266^ -gravity center -extent 266x266 +profile \"*\" $filename_ot");
	exec("$convert_magick -size 266x266 xc:none -fill $filename_ot -draw \"circle 133,133 133,1\" $filename_ot");
	exec("$post_convert --speed 1 -f --ext '.png' $filename_ot ");
}
