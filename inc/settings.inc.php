<?php 

function procommerca_get_settings(){
    return json_decode(get_option('procommerca-settings'), true);
}

function procommerca_set_settings($settings){
    update_option('procommerca-settings', json_encode($settings));
}

?>