<?php

/**
 * MirrorService is a test service that has only one function, mirror, which returns the arguments received. This should be made into a plugin
 * as it shouldn't be in the core distribution, but for now it will stay here. 
 *
 * @author Ariel Sommeria-klein
 */
class MirrorService {

    public function returnOneParam($param){
        return $param;
    }

    public function returnSum($a, $b){
        return $a + $b;
    }
}
?>
