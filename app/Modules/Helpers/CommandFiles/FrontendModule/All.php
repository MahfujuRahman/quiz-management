
<?php

use Illuminate\Support\Str;
/*
|--------------------------------------------------------------------------
|
|--------------------------------------------------------------------------
|
*/

if (!function_exists('ManagementAllPage')) {
    function ManagementAllPage($moduleName)
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
            <All-page permision="[{all=true}]"></All-page>
        </template>

        <script>


        import Allpage from '../../../../../GlobalManagement/{$modulePathForImport}/pages/All.vue';


        export default {
            components: {
                AllPage: Allpage,
            },
        }
        </script>

        <style scoped></style>
        EOD;

        return $content;
    }
}
