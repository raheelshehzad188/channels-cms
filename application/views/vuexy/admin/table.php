<div class="content-body">
                <!-- Data list view starts -->
                <section id="data-list-view" class="data-list-view-header">
                    <div class="action-btns d-none">
                        <div class="btn-dropdown mr-1 mb-1">
                            <div class="btn-group dropdown actions-dropodown">
                                <button type="button" class="btn btn-white px-1 py-1 dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="#"><i class="feather icon-trash"></i>Delete</a>
                                    <a class="dropdown-item" href="#"><i class="feather icon-archive"></i>Archive</a>
                                    <a class="dropdown-item" href="#"><i class="feather icon-file"></i>Print</a>
                                    <a class="dropdown-item" href="#"><i class="feather icon-save"></i>Another Action</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable starts -->
                    <div class="table-responsive">
                        <table class="table data-list-view">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>No</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Video</th>
                                    <th>ORDER STATUS</th> 
                                    <th>PRICE</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $i=1;
                                    $ci->db->where('roleID >',2);
                                        $users = $ci->db->get('users')->result_array();
                                    foreach ($users as $key => $value) {
                                        $value['mediaID'] = 1;
                                        $CI = get_instance();
                                        $CI->load->model('Genres_model');
                                        if(isset($value['mediaID']))
                                        $media = $CI->Genres_model->getMediaByID($value['mediaID']);
                                        if(isset($value['banID']))
                                        $banner = $CI->Genres_model->getMediaByID($value['banID']);
                                        if(isset($value['videoID']))
                                        $videoID = $CI->Genres_model->getMediaByID($value['videoID']);
                                ?>     
                                <tr role="row" class="odd">
                                    <td></td>
                                    <td class=""><?= $i++;?></td>
                                    
                                    <td class="product-img sorting_1">
                                        <?php
                                        if($value['mediaID'] > 0)
                                        {
                                        ?>
                                    <img src="<?= base_url().$media->localPath;?>" style="width: 70px;" alt="No image ">
                                    <?php
                                        }
                                    ?>
                                    </td>
                                    <?php
                                    if(isset($album) && $album)
                                    {
                                        ?>
                                            <td class="product-img sorting_1"><?= $value['videoID']?></td>
                                            <?php
                                        /*if(isset($artist))
                                        {
                                            
                                        }
                                        if(isset($videoID) && $videoID && !isset($artist))
                                        {
                                            ?>
                                            <td class="product-img sorting_1"><a target="_blank" href="<?= base_url().$videoID->localPath;?>">Show</a></td>
                                            <?php
                                        }
                                        else
                                        {
                                            ?>
                                            <td class="product-img sorting_1">Nill</td>
                                            <?php
                                        }*/
                                        ?><?php
                                        if(isset($feature))
                                        {
                                            ?>
                                            <td class="product-name"><?= ($value['feature'] == 0)?'No':'Yes'?></td>
                                            <?php
                                        }
                                    } 
                                        ?>
                                    <td class="product-name">name<?= $value['name']?></td>
                                    
                                    <?php
                                    if(isset($trainner))
                                    {
                                        ?>
                                        <td class="product-img sorting_1">
                                        <?php
                                        if($value['banID'] > 0)
                                        {
                                        ?>
                                    <img src="<?= base_url().$banner->localPath;?>" style="width: 70px;" alt="No image ">
                                    <?php
                                        }
                                    ?>
                                    </td>
                                        <?php
                                    }
                                    ?>
                                    <?php
                                    
                                    if(isset($artist))
                                    {
                                        ?><th><?= $value['discription']?></th><?php
                                    }
                                        ?>
                                        <?php
                                    if(isset($sort))
                                    {
                                        ?><th><?=$value['sort']; ?></th>
                                        <?php
                                    }
                                    ?>
                                    <td>1</td>
                                    <td>2</td>
                                    <td>3</td>
                                    <td class="product-action">
                                        <a href="<?= $url.'/create/'.$value['id'];?>">
                                            <span class="action-edit"><i class="feather icon-edit"></i></span>
                                        </a>
                                        <a href="<?= $url.'/delete/'.$value['id'];?>">
                                            <span class="action-delete"><i class="feather icon-trash"></i></span>
                                        </a>
                                        <?php
                                        if(isset($album))
                                        {
                                            ?>
                                        <a href="<?= $url.'/feature/'.$value['id'];?>">
                                            <span class="action-delete"><i class="feather icon-plus"></i></span>
                                        </a>
                                        <?php
                                        }
                                        ?>
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

            </div>
        