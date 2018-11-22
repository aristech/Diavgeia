<?php
/**
 * @package AristechDiavgeia
 */

 class AristechDiavgeiaActivate
 {
    public static function activate() {
        flush_rewrite_rules();
    }
 }
