<?php
    /**
     * @class  upgletyle
     * @author UPGLE (admin@upgle.com)
     * @brief  upgletyle_plugin_daumview module main class
     **/
    class upgletyle_plugin_daumview extends ModuleObject {

        var $add_triggers = array(
            array('upgletyle.ToolPostManageWrite', 'upgletyle_plugin_daumview', 'controller', 'triggerToolPostManageWrite', 'metabox')
        );

        /**
         * @brief module install
         **/
        function moduleInstall() {
            $oModuleController = &getController('module');

            foreach($this->add_triggers as $trigger) {
                $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
            }

        }

        /**
         * @brief check for update method
         **/
        function checkUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');

            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
            }

            return false;
        }

        /**
         * @brief module update
         **/
        function moduleUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
                    $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
                }
            }
			return new Object(0, 'success_updated');
        }

        /**
         * @brief recompile cache
         **/
        function recompileCache() {
        }

    }
?>
