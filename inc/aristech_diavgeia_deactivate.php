<?php
/**
 * @package AristechDiavgeia
 */

 class AristechDiavgeiaDeactivate
 {
    public static function deactivate() {
        flush_rewrite_rules();
    }
 }