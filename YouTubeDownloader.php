<?php
function getpage($url){
    // fetch data
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 5.0; ASUS_T00J Build/LRX21V) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.133 Mobile Safari/537.36');
    $data = curl_exec($curl);
    curl_close($curl);
    //return preg_replace('~[\r\n]+~', ' ', $data);
    return $data;
}
function format_time($t){
    $return = str_replace('PT','',$t);
    $return = str_replace('H',' hour, ',$return);
    $return = str_replace('M',' minutes and ',$return);
    $return = str_replace('S',' seconds',$return);
    return $return;
}
function get_video_info($v){
    $data = getpage('https://www.youtube.com/get_video_info?video_id='.$v.'&cpn=CouQulsSRICzWn5E&eurl&el=adunit');
    parse_str($data, $data);
    $fmt_list = explode(',',urldecode($data['fmt_list']));
    for($i=0;$i<count($fmt_list);$i++){
        $tmp_var_a = explode('/',$fmt_list[$i]);
        $fmtlist[$tmp_var_a[0]] = $tmp_var_a[1];
    }
    $return['title'] = $data['title'];
    $return['length'] = $data['length_seconds'];
    $return['formatted_length'] = format_time($data['length_seconds']);
    // url encoded fmt stream maps
    $links = explode(',',$data['url_encoded_fmt_stream_map']);
    for($i=0;$i<count($links);$i++){
        parse_str($links[$i]);
        $mime       = explode(';',$type);
        $mime       = str_ireplace('3gpp', '3gp', $mime[0]);;
        $format     = explode('/',$mime);
        $ext        = $format[1];
        $format     = $format[0];
        $resolution = $fmtlist[$itag];
        $tmpvar_b   = explode('x',$fmtlist[$itag]);
        $quality_label = $tmpvar_b[1].'p';
        $return['url_encoded_fmt_stream_map'][$itag] = array(
            'url'          =>$url,
            'ext'          =>ucwords($ext),
            'format'       =>ucwords($format),
            'quality_label'=>$quality_label,
            'resolution'   =>$resolution,
        );
    }
    // adaptive links
    $links = explode(',',$data['adaptive_fmts']);
    for($i=0;$i<count($links);$i++){
        parse_str($links[$i]);
        $mime       = explode(';',$type);
        $mime       = str_ireplace('3gpp', '3gp', $mime[0]);
        $format     = explode('/',$mime);
        $ext        = $format[1];
        $format     = $format[0];
        $resolution = $size;
        if($format=='audio'){
            $quality_label = ceil($bitrate/1024);
            $quality_label = $quality_label.'kbps';
            $resolution = '';
        }
        if($format=='audio'){
            $format = $format.' only';
        }
        if($format=='video'){
            $format = $format.' only';
        }
        $return['adaptive_fmts'][$itag] = array(
            'url'          =>$url,
            'ext'          =>ucwords($ext),
            'format'       =>ucwords($format),
            'quality_label'=>$quality_label,
            'resolution'   =>$resolution,
        );
    }
    return $return;
}

$links = get_video_info($_GET['id']);
echo '<div>Download Links</div><br />
';
echo '<ul>
';
foreach($links['url_encoded_fmt_stream_map'] as $l){
    echo '<a href="'.$l['url'].'&title='.$links['title'].'">Download '.$l['quality_label'].' '.$l['format'].' as '.$l['ext'].' ('.$l['resolution'].') </a><br />
';
}
unset($l);
foreach($links['adaptive_fmts'] as $l){
    echo '<a href="'.$l['url'].'&title='.$links['title'].'">Download '.$l['quality_label'].' '.$l['format'].' as '.$l['ext'].' ('.$l['resolution'].') </a><br />
';
}
echo '</ul>';