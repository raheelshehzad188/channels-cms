
<div class="col-md-6 col-12" style="margin: 0 auto;">
                            <div class="card" style="height: auto;">
                                <div class="card-header">
                                    <h4 class="card-title"><?= $page; ?> </h4>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <form class="form form-vertical" method="post" action="<?= $url.'/save';?>/<?= (isset($edit)?$edit->id:'') ?>" enctype="multipart/form-data">
                                            <?php $this->load->view('flash');?>
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Course</label> 
                                                            <select class="select2 form-control" name="cID" id="basicSelect">
                                                                <?php
                                                                if(isset($edit))
                                                                {
                                                                    foreach ($courses as $key => $value) {
                                                                    ?>
                                                                    <option value="<?= $value['id'];?>" <?= ($value['id'] == $edit->cID)?'selected="true"':'';?>><?= $value['name'];?></option>
                                                                    <?php
                                                                }
                                                                }
                                                                else
                                                                {
                                                                 foreach ($courses as $key => $value) {
                                                                    ?>
                                                                    <option value="<?= $value['id'];?>"><?= $value['name'];?></option>
                                                                    <?php
                                                                }   
                                                                }
                                                                
                                                                ?>
                                                                >
                                                            </select>
                                                        </fieldset>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email-id-vertical">Name</label>
                                                            <input type="text" id="email-id-vertical" class="form-control" name="name" placeholder="Name" value="<?= (isset($edit)?$edit->name:'') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email-id-vertical">"Credits Per Module</label>
                                                            <input type="text" id="email-id-vertical" class="form-control" name="credits" placeholder="Credits" value="<?= (isset($edit)?$edit->credits:'') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 col-12">

                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Image</label>
                                                            <div class="custom-file">
                                                                <input type="file" name="image" class="custom-file-input" id="inputGroupFile01">
                                                                <label class="custom-file-label" for="inputGroupFile01">Choose file</label>
                                                            </div>
                                                        </fieldset>
                                                        <?php
                                                            if(isset($edit) && $edit->mediaID && isset($edit->mediaID))
                                                            {
                                                        $CI = get_instance();

                                                        $CI->load->model('Genres_model');
                                                        $media = $CI->Genres_model->getMediaByID($edit->mediaID);
                                                        ?>
                                                        <img src="<?= base_url().$media->localPath;?>" width="40" height="40" />
                                                        <?php 
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="col-12">
                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Module Description</label>
                                                                <textarea name="detail" class="form-control" id="basicTextarea" rows="3" placeholder="Detail"><?= (isset($edit)?$edit->detail:'') ?></textarea>
                                                        </fieldset>
                                                    </div>
                                                    <div class="col-md-12 col-12">
                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Badge on completion</label>
                                                            <select name="badge" class="form-control" id="basicSelect">
                                                                <?php
                                                                $modal->table = 'badges';
                                                                $badges = $modal->get(array('status'=> 0));
                                                                foreach ($badges as $key => $value) {
                                                                    ?>
                                                                    <option value="<?= $value['id'];?>"><?= $value['name'];?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                                
                                                            </select>
                                                        </fieldset>
                                                    </div>
                                                    
                                                    <div class="col-12">
                                                        <button type="submit" class="btn btn-primary mr-1 mb-1 waves-effect waves-light">Submit</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>