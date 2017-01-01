<?php
$this->Html->addCrumb(ucfirst(Inflector::humanize(Inflector::underscore($controller))), array('action' => 'index'));
$this->Html->addCrumb(ucfirst($title_for_layout), null); 

$this->extend('/Common/admin_add');

$this->assign('title', $title_for_layout);

$this->append('form-start', $this->Form->create($modelClass, array(
        'class' => 'form-horizontal',
        'type' => 'post',
		'enctype'=>'multipart/form-data',
        'novalidate' => true,
        'id' => 'basic_form_validation',
        'inputDefaults' => $this->Layout->inputDefaults
)));

$this->start('form');
echo $this->Form->input('Data.referer', array('value' => $referer, 'class' => 'form-control', 'type' => 'hidden'));
echo $this->Form->input('team_id', array('class' => "bs-select form-control",'label' => array(
                   'text'=> 'Team', 'class' => 'col-md-3 control-label'), 'type' => 'select', 'options' => $teamList, 'data-fv-notempty-message' => __('pleaseSelect')));

echo $this->Form->input('name', array('class' => 'form-control')); ?>

<div class="form-group last <?php echo($this->Form->isFieldError("picture")) ? 'error' : ''; ?>">
    <label class="control-label col-md-3">Image</label>
    <div class="col-md-9">
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="fileinput-new thumbnail">
                <?php
                if (!empty($this->request->data[$modelClass]['picture']) && !empty($this->request->data[$modelClass]['id'])) {
                    echo $this->Image->img('team_member/picture/' . $this->request->data[$modelClass]['id'] . '/' . $this->request->data[$modelClass]['picture'], 200, 200, array(), 'no-image.jpg');
                    echo $this->Form->hidden('old_image', array('value' => $this->request->data[$modelClass]['picture']));
                } else {
                    echo $this->Image->img('', 200, 200, array(), 'no-image.jpg');
                }
                ?>
            </div>

            <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 200px;">
            </div>
            <div>
                <span class="btn default green btn-file" style="width:100%">
                    <span class="fileinput-new">
                        Select image </span>
                    <span class="fileinput-exists">
                        Change </span>
                    <?php echo $this->Form->file('picture', array('type' => 'file', 'class' => 'm-wrap large', 'label' => false)); ?>
                </span>
                <a href="#" class="btn red fileinput-exists" data-dismiss="fileinput" style="width:100%; margin-top: 1px;">
                    Remove </a>
                <?php if ($this->Form->isFieldError("picture")) { ?>
                    <span class="help-block"><?php echo $this->Form->error('picture'); ?></span>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php
echo $this->Form->input('description', array('class' => 'text_editor form-control'));
echo $this->Form->input('status', array('class' => 'icheck'));

$this->end();
$this->start('footer_js');?>
<?php $this->end();
