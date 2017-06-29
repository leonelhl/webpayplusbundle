<?php

/**
 */

namespace LeoX\WebPayPlusBundle\Util;

class Util {
    
    static public function cleanString($real_name, $allowed = array())
    {
        $allow = null;

        if (!empty($allowed)) {
            foreach ($allowed as $value) {
                $allow .= "\\$value";
            }
        }

        $mapping = array(
            'Š' => 'S', 'š' => 's', 'Đ' => 'Dj', 'đ' => 'dj', 'Ž' => 'Z', 'ž' => 'z', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y',
            'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'ű' => 'u',
            'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'Ŕ' => 'R', 'ŕ' => 'r', ' ' => '_', "'" => '_', '/' => '',
            '<' => '', '>' => ''
        );

        if (is_array($real_name)) {

            $cleaned = array();

            foreach ($real_name as $key => $clean) {
                $clean = strtr($clean, $mapping);
                $clean = preg_replace("/[^{$allow}_a-zA-Z0-9.]/", '', $clean);
                $cleaned[$key] = preg_replace('/[_]+/', '_', $clean); // remove double underscore
            }
        } else {
            $real_name = strtr($real_name, $mapping);
            $real_name = preg_replace("/[^{$allow}_a-zA-Z0-9.]/", '', $real_name);
            $cleaned = preg_replace('/[_]+/', '_', $real_name); // remove double underscore
        }
        return $cleaned;
    }
    
}


