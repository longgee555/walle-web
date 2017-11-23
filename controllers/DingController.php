<?php

namespace app\controllers;

use yii;
use app\components\Controller;
use app\models\Task;
use app\models\Project;
use app\models\User;

class DingController extends Controller {

    protected $mobileArr = array(
	    3 => 13317159396,         //zhoukang
	    4 => 18810014483,         //huangjin
	    6 => 18672344215,         //zhangyaodong
	    13 => 18040522280,         //zhoujunlong
	    17 => 15272570611,         //wuwen
	    22 => 17707272442,         //xusheng
	    24 => 18162766745,         //jinchen
	    26 => 13554268637,         //xuxiang
	    28 => 13628698801,         //liujia
	    32 => 13297024600,         //yangyi
	    33 => 13164151240,         //chengchong
    );

    protected $relationArr = array(
	    3 => array(13),
	    4 => array(17, 22, 32),
	    6 => array(24, 26, 28, 33)
    );

    protected function message($message, $mobile) {
	//设置信息
	    $webhook = "https://oapi.dingtalk.com/robot/send?access_token=ffdc4413ce990c96efbec552521ca60f00fca70196ce34768f0d221a8686ad85";   //inf
	//$webhook = "https://oapi.dingtalk.com/robot/send?access_token=d02f551adfc6fdffd7a6e36eae4df2640b2e019badfc7ca6dc4deccc0f3c7872";     //测试
	    $data = array ('msgtype' => 'text','text' => array ('content' =>  $message), 'at' => array ('atMobiles' => $mobile));
	    $data_string = json_encode($data);

	//curl进行dingding操作
	    $ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $webhook);
   	    curl_setopt($ch, CURLOPT_POST, 1);
    	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
   	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_exec($ch);
    	curl_close($ch);
    }

    protected function mobile($id) {
	    $mobile = array();
	    foreach ($this -> relationArr as $key => $value) {
	        if (in_array($id, $value)) {
	    	    array_push($mobile, $this -> mobileArr[$key], $this -> mobileArr[$id]);
            }
	    };
	    return $mobile;
   }

    public function actionCreateMessage($projectId, $info) {
	    $currentUser = User::findOne(Yii::$app -> user -> getId());
	    $project = Project::findOne($projectId);
	    $taskInfo = json_decode($info);

	    if ($project -> level == 3) {
	        $message = $currentUser -> email."创建上线任务，任务内容如下".
	    	        "\n任务创建者：".$currentUser -> email.
		            "\n项目名称：".$project -> name.
	  	            "\n项目环境：".(($project -> level == 3) ? "线上环境" : (($project -> level == 2) ? "预发布环境" : "测试环境")).
		            "\n上线单标题：".$taskInfo -> title.
		            (($project -> level == 3) ? "\n版本号：".$taskInfo -> commit_id : "\n分支：".$taskInfo -> branch."\n上线commmit号：".$taskInfo -> commit_id);

	        $mobile = $this -> mobile($currentUser -> id);

	        $this -> message($message, $mobile);
	    }

        $this -> renderJson(array ('task' => $task, 'user' => $user, 'project' => $project));
    }

    public function actionVerifyMessage($taskId, $status) {
	    $currentUser = User::findOne(Yii::$app -> user -> getId());
	    $task = Task::findOne($taskId);
	    $user = User::findOne($task -> user_id);
	    $project = Project::findOne($task -> project_id);

	    if ($project -> level == 3) {
	        $message = $currentUser -> email.(($status == 0) ? "审核不通过上线任务" : "审核通过了上线任务")."， 任务内容如下".
		            "\n任务创建者：".$user -> email.
		            "\n项目名称：".$project -> name.
	  	            "\n项目环境：".(($project -> level == 3) ? "线上环境" : (($project -> level == 2) ? "预发布环境" : "测试环境")).
		            "\n上线单标题：".$task -> title.
		            (($project -> level == 3) ? "\n版本号：".$task -> commit_id : "\n分支：".$task -> branch."\n上线commmit号：".$task -> commit_id);

	        $mobile = $this -> mobile($currentUser -> id);

	        $this -> message($message, $mobile);
	    }

        $this -> renderJson(array ('task' => $task, 'user' => $user, 'project' => $project));

    }

    public function actionUpdateMessage($taskId, $status) {
	    $currentUser = User::findOne(Yii::$app -> user -> getId());
	    $task = Task::findOne($taskId);
	    $user = User::findOne($task -> user_id);
	    $project = Project::findOne($task -> project_id);

	    if ($project -> level == 3 && false) {
	        $message = $currentUser -> email.(($status == 0) ? "执行任务上线操作" : (($status == 1) ? "执行上线操作成功" : "执行上线操作失败"))."， 任务内容如下".
		            "\n任务创建者：".$user -> email.
		            "\n项目名称：".$project -> name.
	  	            "\n项目环境：".(($project -> level == 3) ? "线上环境" : (($project -> level == 2) ? "预发布环境" : "测试环境")).
		            "\n上线单标题：".$task -> title.
		            (($project -> level == 3) ? "\n版本号：".$task -> commit_id : "\n分支：".$task -> branch."\n上线commmit号：".$task -> commit_id);

	        $mobile = $this -> mobile($currentUser -> id);

	        $this -> message($message, $mobile);
	    }

        $this -> renderJson(array ('task' => $task, 'user' => $user, 'project' => $project));
    }

    public function actionDeleteMessage($taskId) {
	    $currentUser = User::findOne(Yii::$app -> user -> getId());
	    $task = Task::findOne($taskId);
	    $user = User::findOne($task -> user_id);
	    $project = Project::findOne($task -> project_id);

	    if ($project -> level == 3 && false) {
	        $message = $currentUser -> email."执行删除任务操作， 任务内容如下".
		            "\n任务创建者：".$user -> email.
		            "\n项目名称：".$project -> name.
	  	            "\n项目环境：".(($project -> level == 3) ? "线上环境" : (($project -> level == 2) ? "预发布环境" : "测试环境")).
		            "\n上线单标题：".$task -> title.
		            (($project -> level == 3) ? "\n版本号：".$task -> commit_id : "\n分支：".$task -> branch."\n上线commmit号：".$task -> commit_id);

	        $mobile = $this -> mobile($currentUser -> id);

	        $this -> message($message, $mobile);
	    }

        $this -> renderJson(array ('task' => $task, 'user' => $currentUser, 'project' => $project));
    }
}
