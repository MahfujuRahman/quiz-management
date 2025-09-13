<?php

use Illuminate\Support\Str;
/*
|--------------------------------------------------------------------------
|
|--------------------------------------------------------------------------
|
*/

if (!function_exists('ManagementDetailsPage')) {
    function ManagementDetailsPage($moduleName)
    {
           // Check if $moduleName is an array and format it
        $formated_module = [];
        if (is_array($moduleName)) {
            $formated_module = $moduleName;
        } else {
            // If $moduleName is a string, convert it to array if it contains "/"
            $formated_module = explode('/', $moduleName);
        }
    
        // Join array elements with "/" to create the module path
        $modulePathForImport = implode('/', $formated_module);


        $content = <<<EOD
        
        <template>
            <details> </details>
        </template>

        <script>

        import Details from '../../../../../GlobalManagement/{$modulePathForImport}/pages/Details.vue';

        export default {
            components: {
                Details : Details
            },
        
        };
        </script>

        EOD;

        return $content;
    }
}
