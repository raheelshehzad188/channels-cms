<div class="row wrapper border-bottom white-bg page-heading">
	<div class="col-lg-10">
		<h2>Add new Gateway</h2>
	</div>
	<div class="col-lg-2">

	</div>
</div>

<div class="ibox-content">
	<form action="<?=base_url('admin/tags/save');?>" method="post" class="form-horizontal">
		<?php $this->load->view('flash') ?>
		<div class="form-group"><label class="col-sm-2 control-label">Name</label>

			<div class="col-sm-10"><input name="name" type="text" class="form-control"></div>
		</div>
		<div class="form-group"><label class="col-sm-2 control-label">ID</label>

			<div class="col-sm-10"><input name="type"  class="form-control"></div>
		</div>







		<div class="hr-line-dashed"></div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-2">
				<button class="btn btn-white" type="submit">Cancel</button>
				<button class="btn btn-primary" type="submit">Add Gateway</button>
			</div>
		</div>
		</form>
	</div>
