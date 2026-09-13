<div class="mb-5">
  <ul class="m-0 p-0 list-none">
    <li class="inline-block relative top-[3px] text-base text-primary-500 font-Inter">
      <a href="<?= base_url('admin/admin'); ?>">
        <iconify-icon icon="heroicons-outline:home"></iconify-icon>
        <iconify-icon icon="heroicons-outline:chevron-right" class="relative text-slate-500 text-sm rtl:rotate-180"></iconify-icon>
      </a>
    </li>
    <li class="inline-block relative text-sm text-slate-500 font-Inter dark:text-white">
      <?= $page; ?>
    </li>
  </ul>
</div>

<?php $this->load->view('flash'); ?>

<div class="space-y-5">
  <div class="card">
    <header class="card-header noborder flex justify-between items-center flex-wrap gap-3">
      <h4 class="card-title mb-0">Stores</h4>
      <a href="<?= $url; ?>/create" class="btn inline-flex justify-center btn-primary">
        <span class="flex items-center">
          <iconify-icon class="text-xl ltr:mr-2 rtl:ml-2" icon="heroicons-outline:plus"></iconify-icon>
          <span>Add Store</span>
        </span>
      </a>
    </header>
    <div class="card-body px-6 pb-6">
      <div class="overflow-x-auto -mx-6 dashcode-data-table">
        <div class="inline-block min-w-full align-middle">
          <div class="overflow-hidden">
            <table class="min-w-full divide-y divide-slate-100 table-fixed dark:divide-slate-700" id="data-table">
              <thead class="border-t border-slate-100 dark:border-slate-800">
                <tr>
                  <th scope="col" class="table-th">Id</th>
                  <th scope="col" class="table-th">Domain</th>
                  <th scope="col" class="table-th">Created</th>
                  <th scope="col" class="table-th">Status</th>
                  <th scope="col" class="table-th">Action</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                <?php
                $i = 1;
                foreach ($data as $store) {
                        $createdAt = !empty($store['created_at']) ? date('n/j/Y', strtotime($store['created_at'])) : '-';
                        $domain = !empty($store['domain']) ? $store['domain'] : $store['name'];
                        $domainInitial = strtoupper(substr($domain, 0, 1));
                ?>
                <tr>
                  <td class="table-td"><?= $i++; ?></td>
                  <td class="table-td">
                    <span class="flex items-center">
                      <span class="w-7 h-7 rounded-full ltr:mr-3 rtl:ml-3 flex-none bg-primary-500 text-white text-xs font-semibold flex items-center justify-center">
                        <?= $domainInitial; ?>
                      </span>
                      <span class="text-sm text-slate-900 dark:text-slate-300 font-medium"><?= htmlspecialchars($domain); ?></span>
                    </span>
                  </td>
                  <td class="table-td"><?= $createdAt; ?></td>
                  <td class="table-td">
                    <div class="inline-block px-3 min-w-[90px] text-center mx-auto py-1 rounded-[999px] bg-opacity-25 text-success-500 bg-success-500">active</div>
                  </td>
                  <td class="table-td">
                    <div class="relative">
                      <div class="dropdown relative">
                        <button class="text-xl text-center block w-full" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <iconify-icon icon="heroicons-outline:dots-vertical"></iconify-icon>
                        </button>
                        <ul class="dropdown-menu min-w-[140px] absolute text-sm text-slate-700 dark:text-white hidden bg-white dark:bg-slate-700 shadow z-[2] float-left overflow-hidden list-none text-left rounded-lg mt-1 m-0 bg-clip-padding border-none">
                          <li>
                            <a href="<?= base_url('store/login'); ?>?store_domain=<?= urlencode($domain); ?>" target="_blank" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600">Store Login</a>
                          </li>
                          <li>
                            <a href="<?= $url . '/login_as/' . $store['id']; ?>" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600">Login As Store</a>
                          </li>
                          <li>
                            <a href="<?= $url . '/create/' . $store['id']; ?>" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600">Edit</a>
                          </li>
                          <li>
                            <a href="<?= $url . '/delete/' . $store['id']; ?>" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600" onclick="return confirm('Delete this store?');">Delete</a>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </td>
                </tr>
                <?php } ?>
                <?php if (empty($data)): ?>
                <tr>
                  <td class="table-td text-center text-slate-500" colspan="5">No stores found. Click "Add Store" to create one.</td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
