<?php
    /**
     * @class  upgletyleController
     * @author UPGLE (admin@upgle.com)
     * @brief  upgletyle_plugin_daumview module Controller class
     **/

    class upgletyle_plugin_daumviewController extends upgletyle_plugin_daumview {

        /**
         * @brief Initialization
         **/
        function init() {

        }

		function triggerToolPostManageWrite(&$obj) {

			$tpl_path = sprintf('%stpl', $this->module_path);

			Context::set('temp', 'hello');


			$oTemplate = &TemplateHandler::getInstance();
			$obj->daumview = $oTemplate->compile($tpl_path, 'tpl.html');

		}
    }
?>
