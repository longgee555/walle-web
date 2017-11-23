<?php
/**
 * @var yii\web\View $this
 */
$this->title = yii::t('task', 'submit task title');
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\models\Project;
use app\models\Task;

?>
<div class="box">
    <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
      <div class="box-body">
        <?= $form->field($task, 'title')->label(yii::t('task', 'submit title'), ['class' => 'control-label bolder blue']) ?>

        <!-- 分支选取 -->
        <?php if ($conf->repo_mode == Project::REPO_MODE_BRANCH) { ?>
          <div class="form-group">
              <label><?= yii::t('task', 'select branches') ?>
                  <a class="show-tip icon-refresh green" href="javascript:;"></a>
                  <span class="tip"><?= yii::t('task', 'all branches') ?></span>
                  <i class="get-branch icon-spinner icon-spin orange bigger-125" style="display: none"></i>
              </label>
              <select name="Task[branch]" aria-hidden="true" tabindex="-1" id="branch" class="form-control select2 select2-hidden-accessible">
                  <option value="master">master</option>
              </select>
          </div>
        <?php } ?>
        <!-- 分支选取 end -->

        <?= $form->field($task, 'commit_id')->dropDownList([])
          ->label(yii::t('task', 'select branch').'<i class="get-history icon-spinner icon-spin orange bigger-125"></i>', ['class' => 'control-label bolder blue']) ?>

          <!-- 全量/增量 -->
          <div class="form-group">
              <label class="text-right bolder blue">
                  <?= yii::t('task', 'file transmission mode'); ?>
              </label>
              <div id="transmission-full-ctl" class="radio" style="display: inline;" data-rel="tooltip" data-title="<?= yii::t('task', 'file transmission mode full tip') ?>" data-placement="right">
                  <label>
                      <input name="Task[file_transmission_mode]" value="<?= Task::FILE_TRANSMISSION_MODE_FULL ?>" checked="checked" type="radio" class="ace">
                      <span class="lbl"><?= yii::t('task', 'file transmission mode full') ?></span>
                  </label>
              </div>

              <div id="transmission-part-ctl" class="radio" style="display: inline;" data-rel="tooltip" data-title="<?= yii::t('task', 'file transmission mode part tip') ?>" data-placement="right">
                  <label>
                      <input name="Task[file_transmission_mode]" value="<?= Task::FILE_TRANSMISSION_MODE_PART ?>" type="radio" class="ace">
                      <span class="lbl"><?= yii::t('task', 'file transmission mode part') ?></span>
                  </label>
              </div>
          </div>
          <!-- 全量/增量 end -->

          <!-- 文件列表 -->
          <?= $form->field($task, 'file_list')
              ->textarea([
                  'rows'           => 12,
                  'placeholder'    => "index.php\nREADME.md\ndir_name\nfile*",
                  'data-html'      => 'true',
                  'data-placement' => 'top',
                  'data-rel'       => 'tooltip',
                  'data-title'     => yii::t('task', 'file list placeholder'),
                  'style'          => 'display: none',
              ])
              ->label(yii::t('task', 'file list'),
                  ['class' => 'control-label bolder blue', 'style' => 'display: none']) ?>
          <!-- 文件列表 end -->

      </div><!-- /.box-body -->

      <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="<?= yii::t('w', 'submit') ?>">
      </div>

    <!-- 错误提示-->
    <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="800px">
                <div class="modal-header">
                    <button type="button" class="close"
                            data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                    <h4 class="modal-title" id="myModalLabel">
                        <?= yii::t('w', 'modal error title') ?>
                    </h4>
                </div>
                <div class="modal-body"></div>
            </div><!-- /.modal-content -->
        </div>

    </div>
    <!-- 错误提示-->

    <?php ActiveForm::end(); ?>
</div>

<script type="text/javascript">
    jQuery(function($) {
        // 用户上次选择的分支作为转为分支
        var project_id = <?= (int)$_GET['projectId'] ?>;
        var branch_name = 'pre_branch_' + project_id;
        var pre_branch = ace.cookie.get(branch_name);
        if (pre_branch) {
            var option = '<option value="' + pre_branch + '" selected>' + pre_branch + '</option>';
            $('#branch').html(option)
        }

        function getBranchList() {
            $('.get-branch').show();
            $('.tip').hide();
            $('.show-tip').hide();
            $.get("<?= Url::to('@web/walle/get-branch?projectId=') ?>" + <?= (int)$_GET['projectId'] ?>, function (data) {
                // 获取分支失败
                if (data.code) {
                    showError(data.msg);
                }
                var select = '';
                $.each(data.data, function (key, value) {
                    // 默认选中 master 分支
                    var checked = value.id == 'master' ? 'selected' : '';
                    select += '<option value="' + value.id + '"' + checked + '>' + value.message + '</option>';
                });
                $('#branch').html(select);
                $('.get-branch').hide();
                $('.show-tip').show();
                if(data.data.length == 1 || ace.cookie.get(branch_name) != 'master') {
                    // 获取分支完成后, 一定条件重新获取提交列表
                    $('#branch').change();
                }

            });
        }

        function getCommitList() {
            $('.get-history').show();
            $.get("<?= Url::to('@web/walle/get-commit-history?projectId=') ?>" + <?= (int)$_GET['projectId'] ?> +"&branch=" + $('#branch').val(), function (data) {
                // 获取commit log失败
                if (data.code) {
                    showError(data.msg);
                }

                var select = '';
                var arr1 = [];
        		var arr2 = [];
        		var arr3 = [];
        		var arr4 = [];
        		data.data.forEach(function (value, key) {
        		    var m = value.id.split('.').map(function(item) {
        			return parseInt(item, 10)
        		    });
        		    if (m.length !== 3 || !m.every(function(item) {
        			return !isNaN(item)
        		    })) {
        			arr4.push(value);
        			return
        		    }

        		    if (!arr1[m[0]]) {
        		    	arr1[m[0]] = [m];
          		    } else {
        		    	arr1[m[0]].push(m);
        		    }
                        });
        		arr1.forEach(function (value, key) {
        		    arr2 = [];
        		    value.forEach(function (value1, key1) {
        		    	if (!arr2[value1[1]]) {
        		    		arr2[value1[1]] = [value1];
          		    	} else {
        				arr2[value1[1]].push(value1);
        		    	}
          		    });
        		    arr2.forEach(function (value2, key2) {
        		   	value2.sort(function (x, y) {
        				return x[2] - y[2]
        			});
        			arr3 = arr3.concat(value2);
        		    });
                        });

        		arr3.forEach(function (value3, key3) {
        		    arr3[key3] = value3.join('.');
        		});

                arr3.reverse().concat(arr4).forEach(function (value3, key3) {
                    select += '<option value="' + (value3.id || value3) + '">' + (value3.message || value3) + '</option>';
                });
                $('#task-commit_id').html(select);
                $('.get-history').hide()
            });
        }

        $('#branch').change(function() {
            // 添加cookie记住最近使用的分支名字
            ace.cookie.set(branch_name, $(this).val(), 86400*30)
            getCommitList();
        });

        // 页面加载完默认拉取master的commit log
        getCommitList();

        //submit发送dingding
        $('#submit').click(function() {
        	var info = {
                title: $('#task-title').val(),
        	    branch: $('#branch').val(),
        	    commit_id: $('#task-commit_id').val()
        	};

         	$.get("<?= Url::to('@web/ding/create-message') ?>", {projectId: <?= (int)$_GET['projectId'] ?>, info: JSON.stringify(info)})
        })

        // 查看所有分支提示
        $('.show-tip')
            .hover(
            function() {
                $('.tip').show()
            },
            function() {
                $('.tip').hide()
            })
            .click(function() {
                getBranchList();
            });

        // 错误提示
        function showError($msg) {
            $('.modal-body').html($msg);
            $('#myModal').modal({
                backdrop: true,
                keyboard: true,
                show: true
            });
        }

        // 清除提示框内容
        $("#myModal").on("hidden.bs.modal", function () {
            $(this).removeData("bs.modal");
        });

        // 公共提示
        $('[data-rel=tooltip]').tooltip({container:'body'});
        $('[data-rel=popover]').popover({container:'body'});

        // 切换显示文件列表
        $('body').on('click', '#transmission-full-ctl', function() {
            $('#task-file_list').hide();
            $('label[for="task-file_list"]').hide();
        }).on('click', '#transmission-part-ctl', function() {
            $('#task-file_list').show();
            $('label[for="task-file_list"]').show();
        });

    })

</script>
