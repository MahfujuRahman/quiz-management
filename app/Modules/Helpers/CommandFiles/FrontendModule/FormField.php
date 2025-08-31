<?php

use Illuminate\Support\Str;
/*
|--------------------------------------------------------------------------
|
|--------------------------------------------------------------------------
|
*/

if (!function_exists('FormField')) {
    function FormField($fields)
    {


        $content = <<<EOD
        export default [
        EOD;

        if (count($fields)) {
            foreach ($fields as $fieldName) {
                $label = Str::of($fieldName[0])->snake()->replace('_', ' ');
                if (preg_match('/\{.*\}/', $fieldName[1])) {
                    continue;
                }

                $content .= "\n\t{\n";
                $content .= "\t\tname: \"$fieldName[0]\",\n";
                $content .= "\t\tlabel: \"Enter your $label\",\n";

                if (count($fieldName) > 1) {
                    $type = $fieldName[1];
                    $originalType = $type; // Store original type for each field

                    // Handle string with length (e.g., string-255)
                    if (strpos($type, 'string-') === 0) {
                        $type = 'string';
                    }

                    // Handle tinyint and boolean with custom options (e.g., tinyint-yes.no, boolean-active.inactive)
                    if (strpos($type, 'tinyint-') === 0 || strpos($type, 'boolean-') === 0) {
                        // Keep the original type for processing in switch statement
                        $type = strpos($type, 'tinyint-') === 0 ? 'tinyint' : 'boolean';
                    }



                    switch ($type) {
                        case 'longtext':
                        case 'text':
                            $content .= "\t\ttype: \"textarea\",\n";
                            break;
                        case 'date':
                            $content .= "\t\ttype: \"date\",\n";
                            break;
                        case 'month':
                            $content .= "\t\ttype: \"month\",\n";
                            break;
                        case 'datetime':
                        case 'datetime-local':
                        case 'timestamp':
                            $content .= "\t\ttype: \"datetime-local\",\n";
                            break;
                        case 'number':
                        case 'unsigned_int':
                        case 'unsignedInteger':
                        case 'int':
                        case 'integer':
                        case 'intiger':
                        case 'bigint':
                        case 'biginteger':
                            $content .= "\t\ttype: \"number\",\n";
                            break;
                        case 'year':
                            $content .= "\t\ttype: \"number\",\n";
                            $content .= "\t\tmin: \"1900\",\n";
                            $content .= "\t\tmax: \"2100\",\n";
                            break;
                        case 'float':
                            $content .= "\t\ttype: \"number\",\n";
                            $content .= "\t\tstep: \"0.01\",\n";
                            break;
                        case 'double':
                        case 'decimal':
                            $content .= "\t\ttype: \"number\",\n";
                            $content .= "\t\tstep: \"0.01\",\n";
                            break;
                        case 'time':
                            $content .= "\t\ttype: \"time\",\n";
                            break;
                        case 'binary':
                            $content .= "\t\ttype: \"file\",\n";
                            break;
                        case 'uuid':
                            $content .= "\t\ttype: \"text\",\n";
                            $content .= "\t\treadonly: true,\n";
                            break;
                        case 'json':
                            $content .= "\t\ttype: \"textarea\",\n";
                            $content .= "\t\tplaceholder: \"Enter JSON data\",\n";
                            break;
                        case 'string':
                        case 'stringfile':
                        case 'file':
                            if (in_array($fieldName[1], ['stringfile', 'file'])) {
                                $content .= "\t\ttype: \"file\",\n";
                                $content .= "\t\tmultiple: \"false\",\n";
                            } else {
                                $content .= "\t\ttype: \"text\",\n";
                            }
                            break;
                        case 'tinyint':
                        case 'boolean':
                            // Check if it's a custom enum-like format (e.g., tinyint-yes.no or boolean-active.inactive)
                            if (strpos($originalType, 'tinyint-') === 0 || strpos($originalType, 'boolean-') === 0) {
                                $options = explode('.', str_replace(['tinyint-', 'boolean-'], '', $originalType));
                                $content .= "\t\ttype: \"select\",\n";
                                $content .= "\t\tlabel: \"Select $label\",\n";
                                $content .= "\t\tmultiple: false,\n";
                                $content .= "\t\tdata_list: [\n";
                                foreach ($options as $option) {
                                    $content .= "\t\t\t{\n";
                                    $content .= "\t\t\t\tlabel: \"" . ucfirst($option) . "\",\n";
                                    $content .= "\t\t\t\tvalue: \"$option\",\n";
                                    $content .= "\t\t\t},\n";
                                }
                                $content .= "\t\t],\n";
                            } else {
                                // Default Yes/No for regular boolean/tinyint
                                $content .= "\t\ttype: \"select\",\n";
                                $content .= "\t\tlabel: \"Select $label\",\n";
                                $content .= "\t\tmultiple: false,\n";
                                $content .= "\t\tdata_list: [\n";
                                $content .= "\t\t\t{\n";
                                $content .= "\t\t\t\tlabel: \"Yes\",\n";
                                $content .= "\t\t\t\tvalue: \"1\",\n";
                                $content .= "\t\t\t},\n";
                                $content .= "\t\t\t{\n";
                                $content .= "\t\t\t\tlabel: \"No\",\n";
                                $content .= "\t\t\t\tvalue: \"0\",\n";
                                $content .= "\t\t\t},\n";
                                $content .= "\t\t],\n";
                            }
                            break;
                        case strpos($type, 'enum-') === 0:
                            $options = explode('.', str_replace('enum-', '', $type));
                            $content .= "\t\ttype: \"select\",\n";
                            $content .= "\t\tlabel: \"Select $label\",\n";
                            $content .= "\t\tmultiple: false,\n";
                            $content .= "\t\tdata_list: [\n";
                            foreach ($options as $option) {
                                $content .= "\t\t\t{\n";
                                $content .= "\t\t\t\tlabel: \"" . ucfirst($option) . "\",\n";
                                $content .= "\t\t\t\tvalue: \"$option\",\n";
                                $content .= "\t\t\t},\n";
                            }
                            $content .= "\t\t],\n";
                            break;
                        case 'password':
                            $content .= "\t\ttype: \"password\",\n";
                            break;
                        default:
                            $content .= "\t\ttype: \"text\",\n";
                    }
                } else {
                    $content .= "\t\ttype: \"text\",\n";
                }
                $content .= "\t\tvalue: \"\",\n";
                $content .= "\t},\n";
            }
        }

        $content .= "];\n";


        return $content;
    }
}
