<?php
namespace App\Core;

abstract class Controller {
    protected function view( $path,  $data = []) {
        
        extract($data);
        include __DIR__ . '/../Views/' . $path;
    }
}
