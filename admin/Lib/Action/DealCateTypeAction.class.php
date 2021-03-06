<?php
// +----------------------------------------------------------------------
// | Fanwe 方维o2o商业系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://www.fanwe.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 云淡风轻(88522820@qq.com)
// +----------------------------------------------------------------------

class DealCateTypeAction extends CommonAction{
	public function index()
	{
		if(trim($_REQUEST['name'])!='')
		{
			$condition['name'] = array('like','%'.trim($_REQUEST['name']).'%');			
		}
		$this->assign("default_map",$condition);
		parent::index();
		
	}	
	public function add()
	{
		$this->assign("newsort",M(MODULE_NAME)->max("sort")+1);
		$cate_list = M("DealCate")->where("is_delete=0")->findAll();
		$this->assign("cate_list",$cate_list);
		$this->display();
	}
	public function edit() {		
		$id = intval($_REQUEST ['id']);
		$condition['id'] = $id;		
		$vo = M(MODULE_NAME)->where($condition)->find();
		$this->assign ( 'vo', $vo );
		$cate_list = M("DealCate")->where("is_delete=0")->findAll();
		foreach($cate_list as $k=>$v)
		{
			$cate_list[$k]['checked'] = M("DealCateTypeLink")->where("deal_cate_type_id=".$id." and cate_id = ".$v['id'])->count();
		}
		$this->assign("cate_list",$cate_list);
		$this->display ();
	}	
	public function foreverdelete() {
		//彻底删除指定记录
		$ajax = intval($_REQUEST['ajax']);
		$id = $_REQUEST ['id'];
		if (isset ( $id )) {
				$condition = array ('id' => array ('in', explode ( ',', $id ) ) );				
				$rel_data = M(MODULE_NAME)->where($condition)->findAll();				
				foreach($rel_data as $data)
				{
					$info[] = $data['name'];	
				}
				if($info) $info = implode(",",$info);
				$list = M(MODULE_NAME)->where ( $condition )->delete();			
				if ($list!==false) {
					M("DealCateTypeLink")->where(array ('deal_cate_type_id' => array ('in', explode ( ',', $id ) ) ))->delete();
					M("DealCateTypeDealLink")->where(array ('deal_cate_type_id' => array ('in', explode ( ',', $id ) ) ))->delete();
					M("DealCateTypeYouhuiLink")->where(array ('deal_cate_type_id' => array ('in', explode ( ',', $id ) ) ))->delete();
					M("DealCateTypeLocationLink")->where(array ('deal_cate_type_id' => array ('in', explode ( ',', $id ) ) ))->delete();
					save_log($info.l("FOREVER_DELETE_SUCCESS"),1);
								clear_auto_cache("byouhui_filter_nav_cache");
			clear_auto_cache("fyouhui_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("store_filter_nav_cache");
					$this->success (l("FOREVER_DELETE_SUCCESS"),$ajax);
				} else {
					save_log($info.l("FOREVER_DELETE_FAILED"),0);
					$this->error (l("FOREVER_DELETE_FAILED"),$ajax);
				}
			} else {
				$this->error (l("INVALID_OPERATION"),$ajax);
		}
	}
	
	public function insert() {
		B('FilterString');
		$ajax = intval($_REQUEST['ajax']);
		$data = M(MODULE_NAME)->create ();

		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/add"));
		if(!check_empty($data['name']))
		{
			$this->error(L("TYPE_NAME_EMPTY_TIP"));
		}	
		// 更新数据
		$log_info = $data['name'];
		$list=M(MODULE_NAME)->add($data);
		if (false !== $list) {
			foreach($_REQUEST['cate_id'] as $cate_id)
			{
				$data['deal_cate_type_id'] = $list;
				$data['cate_id'] = $cate_id;
				M("DealCateTypeLink")->add($data);
			}
			//成功提示
			save_log($log_info.L("INSERT_SUCCESS"),1);
						clear_auto_cache("byouhui_filter_nav_cache");
			clear_auto_cache("fyouhui_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("store_filter_nav_cache");
			$this->success(L("INSERT_SUCCESS"));
		} else {
			$info = M()->getDbError();
			//错误提示
			save_log($log_info.L("INSERT_FAILED").$info,0);
			$this->error(L("INSERT_FAILED").$info);
		}
	}	
	
	public function update() {
		B('FilterString');
		$data = M(MODULE_NAME)->create ();
		
		
		$log_info = M(MODULE_NAME)->where("id=".intval($data['id']))->getField("name");
		//开始验证有效性
		$this->assign("jumpUrl",u(MODULE_NAME."/edit",array("id"=>$data['id'])));
		if(!check_empty($data['name']))
		{
			$this->error(L("TYPE_NAME_EMPTY_TIP"));
		}	

		// 更新数据
		$list=M(MODULE_NAME)->save ($data);
		if (false !== $list) {
			//成功提示
			M("DealCateTypeLink")->where("deal_cate_type_id=".$data['id'])->delete();
			foreach($_REQUEST['cate_id'] as $cate_id)
			{
				$data['deal_cate_type_id'] = $data['id'];
				$data['cate_id'] = $cate_id;
				M("DealCateTypeLink")->add($data);
			}
			save_log($log_info.L("UPDATE_SUCCESS"),1);
						clear_auto_cache("byouhui_filter_nav_cache");
			clear_auto_cache("fyouhui_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("store_filter_nav_cache");
			$this->success(L("UPDATE_SUCCESS"));
		} else {
			//错误提示
			$info = M()->getDbError();
			//错误提示
			save_log($log_info.L("UPDATE_FAILED").$info,0);
			$this->error(L("UPDATE_FAILED").$info);
		}
	}
	
	public function set_sort()
	{
		$id = intval($_REQUEST['id']);
		$sort = intval($_REQUEST['sort']);
		$log_info = M(MODULE_NAME)->where("id=".$id)->getField("name");
		if(!check_sort($sort))
		{
			$this->error(l("SORT_FAILED"),1);
		}
		M(MODULE_NAME)->where("id=".$id)->setField("sort",$sort);
		save_log($log_info.l("SORT_SUCCESS"),1);
					clear_auto_cache("byouhui_filter_nav_cache");
			clear_auto_cache("fyouhui_filter_nav_cache");
			clear_auto_cache("tuan_filter_nav_cache");
			clear_auto_cache("ytuan_filter_nav_cache");
			clear_auto_cache("store_filter_nav_cache");
		$this->success(l("SORT_SUCCESS"),1);
	}
	
}
?>