<?php 
	
if (! function_exists('get_option')) {
	function get_option($key)
	{
		$option = \App\Option::where('name', $key)->select('script')->first();

		return unserialize($option->script);
	}
}

if (! function_exists('set_option')) {
	function set_option($key, $script)
	{
		$option = new \App\Option();

		$option->name = $key;
		$option->script = serialize($script);
		$option->created_at = date('Y-m-d H:i:s');
		$option->save();

		return unserialize($option->script);
	}
}

if (! function_exists('set_log')) {
	function set_log($model = '', $model_id = null, $action = '')
	{
		$log = new \App\Log();
		$log->user_id = Auth::user()->id;
		$log->model = $model;
		$log->model_id = $model_id;
		$log->action = $action;
		$log->created_at = date('Y-m-d H:i:s');
		$log->save();

		return unserialize($option->script);
	}
}