<!-- Data list view starts -->
                <section id="data-list-view" class="data-list-view-header">

                    <!-- DataTable starts -->
                    <div class="table-responsive">
                        <?php $this->load->view('flash');?>
                        <table class="table data-list-view">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                <?php
                                $i = 1;
                                    foreach ($data as $key => $value) {
                                        $CI = get_instance();
                                        $CI->load->model('Genres_model');
                                        if(false)
                                        {
                                            $modal->table = 'albums';
                                        $img =  $modal->getbyid($value['albumID'])->mediaID;
                                        $media = $CI->Genres_model->getMediaByID($img);

                                        }
                                        if(false)
                                        $video = $CI->Genres_model->getMediaByID($value['audioID']);
                                ?>     
                                <tr role="row" class="odd">
                                    <td><?= $value['id'];?></td>
                                    <td class="product-name"><?= $value['cid']?></td>
                                    <td class="product-name"><?= $value['total']?></td>
                                    <td class="product-name"><?= $value['date_time']?></td>
                                    
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

                    <!-- add new sidebar starts -->
                    <div class="add-new-data-sidebar">
                        <div class="overlay-bg"></div>
                        <div class="add-new-data">
                            <div class="div mt-2 px-2 d-flex new-data-title justify-content-between">
                                <div>
                                    <h4 class="text-uppercase">List View Data</h4>
                                </div>
                                <div class="hide-data-sidebar">
                                    <i class="feather icon-x"></i>
                                </div>
                            </div>
                            <div class="data-items pb-3">
                                <div class="data-fields px-2 mt-3">
                                    <div class="row">
                                        <div class="col-sm-12 data-field-col">
                                            <label for="data-name">Name</label>
                                            <input type="text" class="form-control" id="data-name">
                                        </div>
                                        <div class="col-sm-12 data-field-col">
                                            <label for="data-category"> Category </label>
                                            <select class="form-control" id="data-category">
                                                <option>Audio</option>
                                                <option>Computers</option>
                                                <option>Fitness</option>
                                                <option>Appliance</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-12 data-field-col">
                                            <label for="data-status">Order Status</label>
                                            <select class="form-control" id="data-status">
                                                <option>Pending</option>
                                                <option>Canceled</option>
                                                <option>Delivered</option>
                                                <option>On Hold</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-12 data-field-col">
                                            <label for="data-price">Price</label>
                                            <input type="text" class="form-control" id="data-price">
                                        </div>
                                        <div class="col-sm-12 data-field-col data-list-upload">
                                            <form action="#" class="dropzone dropzone-area" id="dataListUpload">
                                                <div class="dz-message">Upload Image</div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="add-data-footer d-flex justify-content-around px-3 mt-2">
                                <div class="add-data-btn">
                                    <button class="btn btn-primary">Add Data</button>
                                </div>
                                <div class="cancel-data-btn">
                                    <button class="btn btn-outline-danger">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- add new sidebar ends -->
                </section>
                <!-- Data list view end -->