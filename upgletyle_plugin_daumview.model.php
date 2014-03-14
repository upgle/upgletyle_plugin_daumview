<?php
    /**
     * @class  upgletyleController
     * @author UPGLE (admin@upgle.com)
     * @brief  upgletyle_plugin_daumview module Controller class
     **/

    class upgletyle_plugin_daumviewModel extends upgletyle_plugin_daumview {

        /**
         * @brief Initialization
         **/
        function init() {
        }

        /**
         * @brief Check it is joined
         **/
		function isJoined() {
			$user_info = $this->getUserinfo();
			if($user_info->is_joined) return true;
			else return false;
		}

        /**
         * @brief Get a Daumview user info from remote server
         **/
		function getUserinfo($use_cache = true) {

			$user_info = false;

			$oCacheHandler = CacheHandler::getInstance('object', null, true);
			if($oCacheHandler->isSupport())
			{

				//$object_key = 'member_groups:' . getNumberingPath($member_srl) . $member_srl . '_'.$site_srl;
				$object_key = 'user_info';
				$cache_key = $oCacheHandler->getGroupKey('upgletyle_plugin_daumview', $object_key);
				$user_info = $oCacheHandler->get($cache_key);
			}

			if($user_info === false || !$use_cache)
			{
				$url = "http://blog.upgle.com";
				$site_ping = "http://api.v.daum.net/open/user_info.xml?blogurl=".$url;
				$xml = FileHandler::getRemoteResource($site_ping, null, 3, 'GET', 'application/xml');
				if(!$xml) return new Object(-1, 'msg_ping_test_error');

				//XML Parser
				$oXml = new XmlParser();
				$xml_obj = $oXml->parse($xml);

				//Parsing result
				$result_code = $xml_obj->result->head->code->body;
				$result_entity = $xml_obj->result->entity;

				$user_info = new stdClass();
				$user_info->is_joined = ($result_code == 200)? true : false;
				$user_info->profileimage = $result_entity->profileimage->body;
				$user_info->nickname = $result_entity->nickname->body;
				$user_info->blogtitle = $result_entity->blogtitle->body;
				$user_info->blogurl = $result_entity->blogurl->body;
				$user_info->blogrss = $result_entity->blogrss->body;
				$user_info->reporterrss = $result_entity->reporterrss->body;
				$user_info->classes = $result_entity->classes->body;
				$user_info->pluslink = $result_entity->pluslink->body;
				$user_info->fancount = $result_entity->fancount->body;
				$user_info->starcount = $result_entity->starcount->body;
				$user_info->isbestblogger = $result_entity->isbestblogger->body;

				//Put a cache
				if($oCacheHandler->isSupport())
					$oCacheHandler->put($cache_key, $user_info);
			}
			return $user_info;
		}

        /**
         * @brief Get a Daumview categories from remote server
         **/
		function getDaumviewCategories() {

			$daumview_categories = false;

			$oCacheHandler = CacheHandler::getInstance('object', null, true);
			if($oCacheHandler->isSupport())
			{
				$object_key = 'daumview_categories';
				$cache_key = $oCacheHandler->getGroupKey('upgletyle_plugin_daumview', $object_key);
				$daumview_categories = $oCacheHandler->get($cache_key);
			}

			if($daumview_categories === false)
			{
				$site_ping = "http://api.v.daum.net/open/category.xml";
				$xml = FileHandler::getRemoteResource($site_ping, null, 3, 'GET', 'application/xml');
				if(!$xml) return new Object(-1, 'msg_ping_test_error');

				$oXml = new XmlParser();
				$xml_obj = $oXml->parse($xml);
				$one_depth_categories = $xml_obj->result->entity->category;
				if(!is_array($one_depth_categories)) return new Object(-1, 'msg_ping_test_error');

				$daumview_categories = array();
				foreach($one_depth_categories as $one_depth_category) {
					foreach($one_depth_category->list->category as $two_depth_category) {
						$daumview_categories[] = 
							array( 
							 'id' => $two_depth_category->id->body,
							 'name' => $two_depth_category->name->body,
							 'full_name' => $one_depth_category->name->body."(".$two_depth_category->name->body.")",
							 'category_name' => $two_depth_category->category_name->body,
							 'trackback_url' => $two_depth_category->trackback_url->body, 
							 'url' => $two_depth_category->url->body
							);
					}
				}

				//Put a cache
				if($oCacheHandler->isSupport())
					$oCacheHandler->put($cache_key, $daumview_categories);
			}
			return $daumview_categories;
		}

        /**
         * @brief Get a Daumview registrated information by Permalink from remote server
         **/
		function getDaumviewByPermalink($permalink){

			$oXml = new XmlParser();

			$site_ping = "http://api.v.daum.net/open/news_info.xml?permlink=".$permalink;
			$xml = FileHandler::getRemoteResource($site_ping, null, 3, 'GET', 'application/xml');
			if(!$xml) return new Object(-1, 'msg_ping_test_error');
			$xml_obj = $oXml->parse($xml);
			
			$result = new stdClass();
			$result->code = $xml_obj->result->head->code->body; 
			$result->category_id = $xml_obj->result->entity->news->category_id->body;
			$result->id = $xml_obj->result->entity->news->id->body;

			return $result;
		}

    }
?>
