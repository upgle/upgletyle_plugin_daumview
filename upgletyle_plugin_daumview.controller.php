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

		function printWidget(&$oDocument) {

			$document_srl =$oDocument->get('document_srl');
			$output = $this->getDaumviewLog($document_srl);
			if(!$output->toBool() || !$output->data) return;

			$daumview_id = $output->data->daumview_id;
			//if(!$daumview_id) return;

		    //Get a module part config
			$oModuleModel = &getModel('module');
			$module_info = Context::get('module_info');
			$part_config = $oModuleModel->getModulePartConfig('upgletyle_plugin_daumview', $module_info->module_srl);
	
			/*
			'box'=>'박스타입',
			'normal'=>'일반타입',
			'mini'=>'미니타입',
			'button'=>'버튼타입',
			*/
			if($part_config->widget_type == 'box')
				$compiled_widget = sprintf("<iframe width=\"100%%\" height=\"90\" src=\"http://api.v.daum.net/widget1?nid=%s\"	frameborder=\"no\" scrolling=\"no\" allowtransparency=\"true\"></iframe>", $daumview_id);
			elseif($part_config->widget_type == 'normal')
				$compiled_widget = sprintf("<iframe width=\"100%%\" height=\"44\" src=\"http://api.v.daum.net/widget3?nid=%s\" frameborder=\"no\" scrolling=\"no\" allowtransparency=\"true\"></iframe>", $daumview_id);
			elseif($part_config->widget_type == 'mini')
				$compiled_widget = sprintf("<iframe width=\"100%%\" height=\"30\" src=\"http://api.v.daum.net/widget4?nid=%s\" frameborder=\"no\" scrolling=\"no\" allowtransparency=\"true\"></iframe>", $daumview_id);
			elseif($part_config->widget_type == 'button')
				$compiled_widget = sprintf("<iframe width=\"100%%\" height=\"90\" src=\"http://api.v.daum.net/widget2?nid=%s\" frameborder=\"no\" scrolling=\"no\" allowtransparency=\"true\"></iframe>", $daumview_id);
		
			return $compiled_widget;
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

			//Set a template file
			if($daumview_info->status == 'PUBLISHED') {
				$tpl_file = "published.html";
			}
			else {
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
				//Update Daumview Log
				$args = new stdClass();
				$args->document_srl = $obj->document_srl;
				$args->module_srl = $obj->module_srl;
				$args->category_id = $obj->plugin_daumview;
				$output = $this->updateDaumviewLog($args);
			}
			else {
				//Insert Daumview Log
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
		function triggerPublishObjectPublish($oDocument) {

			$document_srl = $oDocument->document_srl;
			$output = $this->getDaumviewLog($document_srl);
			if(!$output->toBool()) return;

			if($output->data && $output->data->status == 'NOT_PUBLISHED')
			{
				$this->sendDaumview($oDocument,$output->data->category_id);
			}
		}

		function syncDaumviewCategories() {

			$oDaumviewModel = getModel('upgletyle_plugin_daumview');
			$oDaumviewModel->getDaumviewCategories(false);

			$this->setMessage('다음서버와 동기화되었습니다.');
		}

		function procUpgletyle_plugin_daumviewUpdateConfig() {

			$vars = Context::getRequestVars();
			$module_info = Context::get('module_info');

			$oModuleModel = &getModel('module');
            $oModuleController = &getController('module');			
			
			//Get a module part config
			$part_config = $oModuleModel->getModulePartConfig('upgletyle_plugin_daumview', $module_info->module_srl);

			//Insert a module part config
			$part_config->widget_type = $vars->widget_type;
            $oModuleController->insertModulePartConfig('upgletyle_plugin_daumview', $module_info->module_srl, $part_config);

			$this->setMessage('success_updated');
			$this->setRedirectUrl(Context::get('success_return_url'));
		}

		function sendDaumview($oDocument, $category_id)
		{
			$oModuleController = &getController('module');
			$oDaumviewModel = getModel('upgletyle_plugin_daumview');

			$trackback_url = $oDaumviewModel->getTrackbackUrlByCategory($category_id);

			// Information sent by
			$http = parse_url($trackback_url);

			$obj->blog_name = str_replace(array('&lt;','&gt;','&amp;','&quot;'), array('<','>','&','"'), Context::getBrowserTitle());
			$oModuleController->replaceDefinedLangCode($obj->blog_name);
			$obj->title = $oDocument->getTitleText();
			$obj->excerpt = $oDocument->getSummary(200);
			$obj->url = getFullUrl('','document_srl',$oDocument->document_srl);

			$content = sprintf(
				"title=%s&".
				"url=%s&".
				"blog_name=%s&".
				"excerpt=%s",
				urlencode($obj->title),
				urlencode($obj->url),
				urlencode($obj->blog_name),
				urlencode($obj->excerpt)
			);

			$buff = FileHandler::getRemoteResource($trackback_url, $content, 3, 'POST', 'application/x-www-form-urlencoded');

			$oXmlParser = new XmlParser();
			$xmlDoc = $oXmlParser->parse($buff);

			$error = $xmlDoc->response->error->body;
			if($error) 
			{
				$message = $xmlDoc->response->message->body;
				$this->updateErrorMessage($oDocument->document_srl, $message);
			}
			elseif(!$error)
			{
				$daumview_id = trim($xmlDoc->response->id->body);
				$args = new stdClass();
				$args->document_srl = $oDocument->document_srl;
				$args->daumview_id = $daumview_id;
				$args->status = 'PUBLISHED';
				$args->message = '';
				$this->updateDaumviewLog($args);
			}
		}

		function getDaumviewLog($document_srl)
		{
			$args->document_srl = $document_srl;
            $output = executeQuery('upgletyle_plugin_daumview.getDaumview', $args);
            return $output;
		}

		function insertDaumviewLog($args)
		{
            $output = executeQuery('upgletyle_plugin_daumview.insertDaumview',$args);
            return $output;
		}

		function updateDaumviewLog($args)
		{
            $output = executeQuery('upgletyle_plugin_daumview.updateDaumview',$args);
            return $output;
		}

		function deleteDaumviewLog($document_srl)
		{
            $args->document_srl = $document_srl;
            $output = executeQuery('upgletyle_plugin_daumview.deleteDaumview',$args);
            return $output;
		}

		function updateErrorMessage($document_srl,$message){
			$args = new stdClass();
			$args->document_srl = $document_srl;
			$args->message = $message;
            $output = executeQuery('upgletyle_plugin_daumview.updateDaumview',$args);
            return $output;
		}
    }
?>
