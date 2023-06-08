<section id="multiple-column-form">
                    <div class="row match-height">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-content">
                                    <div class="card-body">
                                        <form class="form form-vertical" method="post" action="<?= $url.'/save';?>/<?= (isset($edit)?$edit->id:'') ?>" enctype="multipart/form-data">
                                            <?php $this->load->view('flash');?>
                                            <div class="form-body">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="form-group">
                                                            <label for="email-id-vertical">Title</label>
                                                            <input type="text" id="email-id-vertical" class="form-control" name="title" placeholder="Title" value="<?= (isset($edit)?$edit->title:'') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">

                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Image</label>
                                                            <div class="custom-file">
                                                                <input type="file" name="image" class="custom-file-input" id="inputGroupFile01">
                                                                <label class="custom-file-label" for="inputGroupFile01">Choose file</label>
                                                            </div>
                                                        </fieldset>
                                                        <?php
                                                            if(isset($edit) && $edit->mediaID)
                                                            {
                                                                $CI = get_instance();

                                                                $CI->load->model('Genres_model');
                                                                $media = $CI->Genres_model->getMediaByID($edit->mediaID);
                                                                if(isset($media->localPath))
                                                                {
                                                                    ?>
                                                                    <img src="<?= base_url().$media->localPath;?>" width="40" height="40" />
                                                                    <?php
                                                                }
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Vimeo Video Id</label>
                                                            <div class="custom-file">
                                                                <input type="text" id="email-id-vertical" class="form-control" name="videoID" placeholder="Video ID" value="<?= (isset($edit)?$edit->videoID:'') ?>">
                                                            </div>
                                                        </fieldset>
                                                        
                                                        <!--
                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Video</label>
                                                            <div class="custom-file">
                                                                <input type="file" name="video" class="custom-file-input" id="inputGroupFile01">
                                                                <label class="custom-file-label" for="inputGroupFile01">Choose file</label>
                                                            </div>
                                                        </fieldset>
                                                        <?php
                                                            if(isset($edit) && $edit->mediaID)
                                                            {
                                                        $CI = get_instance();

                                                        $CI->load->model('Genres_model');
                                                        $media = $CI->Genres_model->getMediaByID($edit->mediaID);
                                                        ?>
                                                        <img src="<?= base_url().$media->localPath;?>" width="40" height="40" />
                                                        <?php
                                                        }
                                                        ?>-->
                                                    </div>
                                                    <div class="col-12">
                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Description / What you will learn</label>
                                                                <textarea name="discription" class="form-control" id="basicTextarea" rows="3" placeholder="Discription"></textarea>
                                                        </fieldset>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Course</label>
                                                            <select name="cID" onchange="vid_course()" class="form-control" id="vidless">
                                                                <?php
                                                                foreach ($courses as $key => $value) {
                                                                    ?>
                                                                    <option value="<?= $value['id'];?>" <?= (isset($edit) && $edit->cID == $value['id'])?"selected":"" ?>><?= $value['name'];?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                                
                                                            </select>
                                                        </fieldset>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <fieldset class="form-group">
                                                            <label for="basicInputFile">Module</label>
                                                            <select name="mID" class="form-control" id="vid_mod">
                                                                <?php
                                                                foreach ($modules as $key => $value) {
                                                                    ?>
                                                                    <option value="<?= $value['id'];?>" <?= (isset($edit) && $edit->mID == $value['id'])?"selected":"" ?>><?= $value['name'];?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                                
                                                            </select>
                                                        </fieldset>

                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <input type="checkbox" name="after_lesson" value="0" id="after_lesson">
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                    </div>
                                                    <div class="col-md-12 col-12" id="lesson_div">
                                                        <div class="col-md-6 col-12">
                                                            <fieldset class="form-group">
                                                                <label for="basicInputFile">Select Quiz</label>
                                                                <select name="mID" class="form-control" id="vid_mod">
                                                                    <option value="">Select Quiz</option>
                                                                    <?php
                                                                    foreach ($quiz as $key => $value) {
                                                                        // var_dump($value['id']);
                                                                        ?>
                                                                        <option value="<?= $value['id'];?>" <?= (isset($edit) && $edit->mID == $value['id'])?"selected":"" ?>><?= $value['name'];?></option>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                    
                                                                </select>
                                                            </fieldset>
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
                    </div>
                </section>
                