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


        /**
         * @brief triggerToolPostManageWrite
         **/
		function triggerToolPostManageWrite(&$obj) {

			$document_srl = Context::get('document_srl');
			$oDaumviewModel = getModel('upgletyle_plugin_daumview');

			//If it is not joined, return.
			if(!$oDaumviewModel->isJoined()) return;

			//Get a saved daumview info
			if($document_srl) {
				$output = $this->getDaumviewLog($document_srl);
				if(!$output->toBool()) return;

				$daumview_info = $output->data;
				Context::set('daumview_info', $daumview_info);
			}


			if($daumview_info->status == 'PUBLISHED') {
				$tpl_file = "published.html";
			}
			else {
				//Set a daumview categories
				$daumview_categories = $oDaumviewModel->getDaumviewCategories();
				Context::set('daumview_categories', $daumview_categories);
				$tpl_file = "not_published.html";
			}

			//Set a Template
			$oTemplate = &TemplateHandler::getInstance();
			$tpl_path = sprintf('%stpl', $this->module_path);
			$obj->daumview = $oTemplate->compile($tpl_path, $tpl_file);
		}

		

        /**
         * @brief triggerProcUpgletylePostsave
         **/
		function triggerProcUpgletylePostsave($obj) {

			//카테고리를 선택하지 않았다면 리턴
			if(!$obj->plugin_daumview) return;

			//DB가 있는지 확인
			$output = $this->getDaumviewLog($obj->document_srl);
			if(!$output->toBool()) return;

			//이미 송고되어있다면 리턴
			if($output->data->status == 'PUBLISHED') return;
			
			if($output->data) {
				//업데이트
				$args = new stdClass();
				$args->document_srl = $obj->document_srl;
				$args->module_srl = $obj->module_srl;
				$args->category_id = $obj->plugin_daumview;
				$output = $this->updateDaumviewLog($args);
			}
			else {
				//db가 없으니  등록
				$args = new stdClass();
				$args->document_srl = $obj->document_srl;
				$args->module_srl = $obj->module_srl;
				$args->category_id = $obj->plugin_daumview;
				$args->daumview_id = 0;
				$output = $this->insertDaumviewLog($args);
			}

		}



        /**
         * @brief triggerPublishObjectPublish
         **/
		function triggerPublishObjectPublish($obj) {

			//debugPrint($obj);

		}


		function getDaumviewLog($document_srl){
			$args->document_srl = $document_srl;
            $output = executeQuery('upgletyle_plugin_daumview.getDaumview', $args);
            return $output;
		}
		function insertDaumviewLog($args){
            $output = executeQuery('upgletyle_plugin_daumview.insertDaumview',$args);
            return $output;
		}
		function updateDaumviewLog($args){
            $output = executeQuery('upgletyle_plugin_daumview.updateDaumview',$args);
            return $output;
		}
		function deleteDaumviewLog($document_srl){
            $args->document_srl = $document_srl;
            $output = executeQuery('upgletyle_plugin_daumview.deleteDaumview',$args);
            return $output;
		}


    }
?>
