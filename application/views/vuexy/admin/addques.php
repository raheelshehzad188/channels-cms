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
                                                            <label for="email-id-vertical"> Quiz Type </label>
                                                            <select name="quiz" class="form-control" id="quiz">
                                                                <option value="">Select Quiz Type</option>
                                                                <?php
                                                                $types = array(
                                                                    '0'=> "Course",
                                                                    '1'=> "Module"
                                                                );
                                                                $modal->table = 'quiz';
                                                                $all = $modal->get(array());
                                                                foreach ($all as $key => $value) {
                                                                    ?>
                                                                    <option value="<?= $value['id'];?>" <?= (isset($edit) && $edit->btype == $value['id'])?"selected":"" ?>><?= $value['name'];?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                                
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email-id-vertical"> Question </label>
                                                            <input type="text" id="email-id-vertical" class="form-control" name="name" placeholder="Name" value="<?= (isset($edit)?$edit->name:'') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email-id-vertical"> Question Option </label>
                                                            <textarea class="form-control" name="option"><?= (isset($edit)?$edit->option:'') ?></textarea>
                                                            <span>cmma sepreated opion</span>
                                                        </div>
                                                    </div>

                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email-id-vertical"> Question Awnser </label>
                                                            <input type="text" id="email-id-vertical" class="form-control" name="ans" placeholder="Name" value="<?= (isset($edit)?$edit->ans:'') ?>">
                                                        </div>
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