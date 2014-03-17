<?php
    /**
     * @class  upgletyleController
     * @author UPGLE (admin@upgle.com)
     * @brief  upgletyle_plugin_daumview module Controller class
     **/

    class upgletyle_plugin_daumviewView extends upgletyle_plugin_daumview {

        /**
         * @brief Initialization
         **/
        function init() {
        }

		function dispPluginConfig() {

			$oModuleModel = getModel('module');
			$oDaumviewModel = getModel('upgletyle_plugin_daumview');

			//Get a module part config
			$module_info = Context::get('module_info');
			$part_config = $oModuleModel->getModulePartConfig('upgletyle_plugin_daumview', $module_info->module_srl);
			Context::set('part_config', $part_config);

			//Set a Remote Userinfo
			$user_info = $oDaumviewModel->getUserinfo(false);
			Context::set('user_info', $user_info);

			$categories = $oDaumviewModel->getDaumviewCategories();
			Context::set('categories', $categories);

			//Set a Template
			$oTemplate = &TemplateHandler::getInstance();
			$tpl_path = sprintf('%stpl', $this->module_path);
			$compiled = $oTemplate->compile($tpl_path, 'config.html');

			return $compiled;
		}

    }
?>
