<div class="mb-5">
  <ul class="m-0 p-0 list-none">
    <li class="inline-block relative top-[3px] text-base text-primary-500 font-Inter">
      <a href="<?= base_url('admin/admin'); ?>">
        <iconify-icon icon="heroicons-outline:home"></iconify-icon>
        <iconify-icon icon="heroicons-outline:chevron-right" class="relative text-slate-500 text-sm rtl:rotate-180"></iconify-icon>
      </a>
    </li>
    <li class="inline-block relative text-sm text-primary-500 font-Inter">
      <a href="<?= $url . '/all'; ?>">Stores</a>
      <iconify-icon icon="heroicons-outline:chevron-right" class="relative top-[3px] text-slate-500 rtl:rotate-180"></iconify-icon>
    </li>
    <li class="inline-block relative text-sm text-slate-500 font-Inter dark:text-white">
      <?= $page; ?>
    </li>
  </ul>
</div>

<div class="space-y-5">
  <div class="card max-w-2xl">
    <header class="card-header noborder">
      <h4 class="card-title"><?= $page; ?></h4>
    </header>
    <div class="card-body px-6 pb-6">
      <form class="space-y-4" method="post" action="<?= $url . '/save/' . (isset($edit) ? $edit->id : ''); ?>">
        <?php $this->load->view('flash'); ?>

        <div class="input-area">
          <label for="domain" class="form-label">Domain Name <span class="text-danger-500">*</span></label>
          <input type="text" id="domain" name="domain" class="form-control" placeholder="example.com" value="<?= isset($edit) ? htmlspecialchars($edit->domain) : ''; ?>" required>
        </div>

        <div class="input-area">
          <label for="password" class="form-label">
            Password <?= isset($edit) ? '' : '<span class="text-danger-500">*</span>'; ?>
          </label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" <?= isset($edit) ? '' : 'required'; ?>>
          <?php if (isset($edit)) { ?>
          <p class="text-xs text-slate-500 mt-1">Leave blank to keep current password.</p>
          <?php } ?>
        </div>

        <div class="input-area">
          <label for="confirm_password" class="form-label">
            Confirm Password <?= isset($edit) ? '' : '<span class="text-danger-500">*</span>'; ?>
          </label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm password" <?= isset($edit) ? '' : 'required'; ?>>
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
          <button type="submit" class="btn inline-flex justify-center btn-primary">
            <?= isset($edit) ? 'Update Store' : 'Create Store'; ?>
          </button>
          <a href="<?= $url . '/all'; ?>" class="btn inline-flex justify-center btn-secondary">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
