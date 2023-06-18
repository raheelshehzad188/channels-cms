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
                                                        <div class="form-group">
                                                            <label for="email-id-vertical">Quiz Name</label>
                                                            <input type="text" id="email-id-vertical" class="form-control" name="name" placeholder="Name" value="<?= (isset($edit)?$edit->name:'') ?>">
                                                        </div>
                                                    </div>
                                                        <div class="col-md-12 col-12">
                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Quiz Type</label>
                                                            <select name="btype" class="form-control" id="vidless">
                                                                <?php
                                                                $types = array(
                                                                    '0'=> "Course",
                                                                    '1'=> "Module"
                                                                );
                                                                foreach ($types as $key => $value) {
                                                                    ?>
                                                                    <option value="<?= $key;?>" <?= (isset($edit) && $edit->btype == $key)?"selected":"" ?>><?= $value;?></option>
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