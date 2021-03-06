<h1 class="heading1">
  <span class="maintext"><i class="fa fa-group"></i> <?php echo $heading_title; ?></span>
  <span class="subtext"></span>
</h1>

<?php if ($success) { ?>
<div class="alert alert-success">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<?php echo $success; ?>
</div>
<?php } ?>

<?php if ($error_warning) { ?>
<div class="alert alert-error alert-danger">
<button type="button" class="close" data-dismiss="alert">&times;</button>
<?php echo $error_warning; ?>
</div>
<?php } ?>

<div class="contentpanel">
	<?php echo $form['form_open']; ?>
	
	<p><?php echo $text_account_already; ?></p>
	
	<h4 class="heading4"><?php echo $text_your_details; ?></h4>
	<div class="registerbox form-horizontal">
		<fieldset>
		<?php
			foreach ($form['fields']['general'] as $field_name => $field) {
				//todo: remove this in the next major release
				if($field_name == 'loginname'){ continue;} 	?>
			<div class="form-group <?php echo ${'error_'.$field_name} ? 'has-error' : ''; ?>">
				<label class="control-label col-sm-4"><?php echo ${'entry_'.$field_name}; ?></label>
				<div class="input-group col-sm-4">
				    <?php echo $field; ?>
				</div>
				<span class="help-block"><?php echo ${'error_'.$field_name}; ?></span>
			</div>		
		<?php
			}
		?>	
		</fieldset>
	</div>

	<h4 class="heading4"><?php echo $text_your_address; ?></h4>
	<div class="registerbox form-horizontal ">
		<fieldset>
		<?php
			foreach ($form['fields']['address'] as $field_name=>$field) {?>
			<div class="form-group <?php if (${'error_'.$field_name}) echo 'has-error'; ?>">
				<label class="control-label col-sm-4"><?php echo ${'entry_'.$field_name}; ?></label>
				<div class="input-group col-sm-4">
				    <?php echo $field; ?>
				</div>
				<span class="help-block"><?php echo ${'error_'.$field_name}; ?></span>				
			</div>		
		<?php
			}
		?>	
		</fieldset>
	</div>
	
	<h4 class="heading4 "><?php echo $text_login_details; ?></h4>
	<div class="registerbox form-horizontal">
		<fieldset>
			<?php if (isset($form['fields']['general']['loginname'])) { ?>
			<div class="form-group <?php if ($error_loginname) echo 'has-error'; ?>">
				<label class="col-sm-4 control-label"><?php echo $entry_loginname; ?></label>
				<div class="input-group col-sm-4">
				    <?php echo $form['fields']['general']['loginname']; ?>
				</div>
				<span class="help-block"><?php echo $error_loginname; ?></span>
			</div>
			<?php } ?>
			<div class="form-group <?php if ($error_password) echo 'has-error'; ?>">
				<label class="col-sm-4 control-label"><?php echo $entry_password; ?></label>
				<div class="input-group col-sm-4">
				    <?php echo $form['fields']['password']['password']; ?>
				</div>
				<span class="help-block"><?php echo $error_password; ?></span>
			</div>
			<div class="form-group <?php if ($error_confirm) echo 'has-error'; ?>">
				<label class="col-sm-4 control-label"><?php echo $entry_confirm; ?></label>
				<div class="input-group col-sm-4">
				    <?php echo $form['fields']['password']['confirm']; ?>
				</div>
				<span class="help-block"><?php echo $error_confirm; ?></span>
			</div>
		</fieldset>
	</div>
	
	<?php echo $this->getHookVar('customer_attributes'); ?>
	
	<h4 class="heading4 "><?php echo $text_newsletter; ?></h4>
	<div class="registerbox form-horizontal">
		<fieldset>
			<div class="form-group">
				<label class="col-sm-4 control-label"><?php echo $entry_newsletter; ?></label>
				<div class="input-group col-sm-4">
				    <?php echo $form['fields']['newsletter']['newsletter']; ?>
				</div>
			</div>
		</fieldset>
		<?php if ($form['fields']['newsletter']['captcha']) { ?>
		<fieldset>
			<div class="form-group <?php if ($error_captcha) echo 'has-error'; ?>">
				<?php if ($form['fields']['newsletter']['captcha']->type == 'recaptcha') { ?>
				<label class="col-sm-4 control-label"></label>
				<?php } else { ?>
				<label class="col-sm-4 control-label"><?php echo $entry_captcha; ?></label>
				<?php } ?>
				<div class="input-group col-sm-4">
				    <?php echo $form['fields']['newsletter']['captcha']; ?>
				</div>
				<span class="help-block"><?php echo $error_captcha; ?></span>
			</div>
		</fieldset>
		<?php } ?>
	</div>


	<div class="form-group">
		<div class="col-md-12">
	<?php if ($text_agree) { ?>
			<label class="col-md-6 mt20 mb40">
				<?php echo $text_agree; ?>&nbsp;<a href="<?php echo $text_agree_href; ?>" onclick="openModalRemote('#privacyPolicyModal','<?php echo $text_agree_href; ?>'); return false;"><b><?php echo $text_agree_href_text; ?></b></a>

				<?php echo $form['agree']; ?>
			</label>

	<?php } ?>    	
	    	<div class="col-md-2 mt20 mb40">
	    		<button class="btn btn-orange pull-right lock-on-click" title="<?php echo $form['continue']->name ?>" type="submit">
	    		    <i class="fa fa-check"></i>
	    		    <?php echo $form['continue']->name ?>
	    		</button>
	    	</div>	
	    </div>
	</div>
	
</form>
</div>

<div id="privacyPolicyModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="privacyPolicyModalLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">??</button>
		<h3 id="privacyPolicyModalLabel"><?php echo $text_agree_href_text; ?></h3>
	</div>
	<div class="modal-body">
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo $text_close; ?></button>
	</div>
</div>
</div>
</div>  

<script type="text/javascript">
<?php $cz_url = $this->html->getURL('common/zone', '&zone_id='. $zone_id); ?>
$('#AccountFrm_country_id').change( function(){
    $('select[name=\'zone_id\']').load('<?php echo $cz_url;?>&country_id=' + $(this).val());
});
$('select[name=\'zone_id\']').load('<?php echo $cz_url;?>&country_id='+ $('#AccountFrm_country_id').val());
</script>