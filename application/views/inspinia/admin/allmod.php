<!-- Data list view starts -->
                <section id="data-list-view" class="data-list-view-header">

                    <!-- DataTable starts -->
                    <div class="table-responsive">
                        <a class="btn btn-outline-primary" href="<?= $url; ?>/create" >Add Module</a>
                        <?php $this->load->view('flash');?>
                        <table class="table data-list-view">
                            <thead>
                                <tr>
                                    <th class="d-none"></th>
                                    <th>No</th>
                                    <th>Label</th>
                                    <th>Course</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                <?php
                                    $i=1;
                                         $CI = get_instance();
                                        $CI->load->model('Common_model');
		$modal = $CI->Common_model;
                                    foreach ($data as $key => $value) {
                                        $modal->table = 'courses';
                                        $modal->key = 'id';
                                        $cour = $modal->getbyid($value['cID']);
                                ?>     
                                <tr role="row" class="odd">
                                    <td class=""><?= $i++;?></td>
                                    
                                    <td class="product-name"><?= $value['name']?></td>
                                    <td class="product-name"><?= (isset($cour->name)?$cour->name:"")?></td>
                                    <td class="product-action">
                                        <a href="<?= $url.'/create/'.$value['id'];?>">
                                            <span class="action-edit"><i class="feather icon-edit"></i></span>
                                        </a>
                                        <a href="<?= $url.'/delete/'.$value['id'];?>">
                                            <span class="action-delete"><i class="feather icon-trash"></i></span>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                        }
                                     ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- DataTable ends -->
                </section>
                <!-- Data list view end -->