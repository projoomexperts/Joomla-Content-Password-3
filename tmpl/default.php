<?php defined('_JEXEC') or die(); ?>
<div class="contentpassword">
	<h3 class="contentpassword-title"><?php echo $this->title ?></h3>
	<p class="contentpassword-description">
	<?php echo $this->description; ?>
	<?php if ($this->error): ?>
		<br /><span class="contentpassword-error"><?php echo JText::_('PLG_CONTENTPASSWORD_FORM_ERROR'); ?></span>
	<?php endif; ?>
	</p>
	<form class="contentpassword-form" name="contentpassword_form" method="post" action="<?php echo $this->action ?>">
		<label for="<?php echo $this->formid; ?>" class="contentpassword-label" ><?php echo JText::_('PLG_CONTENTPASSWORD_FORM_LABEL'); ?></label>
		<input type="password" id="<?php echo $this->formid; ?>" class="contentpassword-password" name="contentpassword_password" />
		<input type="submit" class="contentpassword-submit" name="contentpassword_submit" value="<?php echo JText::_('PLG_CONTENTPASSWORD_FORM_SUBMIT'); ?>" />
	</form>
</div>