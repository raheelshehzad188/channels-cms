<?php
$flashError = ec_take_flash('error');
$flashSuccess = ec_take_flash('success');

if ($flashError) {
	?>
	<div class="alert alert-danger">
  <?= $flashError ?>
</div>
	<?php
}

if (isset($error)) {
	?>
	<div class="alert alert-danger">
  <?= $error; ?>
</div>
	<?php
}

if ($flashSuccess) {
	?>
	<div class="alert alert-success">
  <?= $flashSuccess ?>
</div>
	<?php
}

if (isset($success)) {
	?>
	<div class="alert alert-success">
  <?= $success; ?>
</div>
	<?php
}
?>
