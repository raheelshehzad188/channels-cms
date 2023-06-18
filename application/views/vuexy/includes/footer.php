            </div>
        </div>
    </div>
<!-- BEGIN: Footer-->
    <footer class="footer footer-static footer-light">
        <p class="clearfix blue-grey lighten-2 mb-0"><span class="float-md-left d-block d-md-inline-block mt-25">COPYRIGHT &copy; 2020<a class="text-bold-800 grey darken-2" href="http://Channelsmedia.com/" target="_blank">Channelsmedia.com,</a>All rights Reserved</span><span class="float-md-right d-none d-md-block">Hand-crafted & Made with<i class="feather icon-heart pink"></i></span>
            <button class="btn btn-primary btn-icon scroll-top" type="button"><i class="feather icon-arrow-up"></i></button>
        </p>
    </footer>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script type="text/javascript">
        var BASE_URL = "<?=(isset($url)?$url:base_url())?>";
    </script>
    <!-- END: Footer-->
    <!-- START: Send Sms-->
    <!-- Modal -->
  <div class="modal fade" id="sndSmsModal" role="dialog">
    <div class="modal-dialog">
    <div class="forloader"><div class="loader"></div></div>
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"></h4>
        </div>
        <div class="modal-body">
          <div class="form">
              <input type="text" name=" number" id="cnum" class="form-control"/>
              <input type="texxt" name="couponID" id = "couponID"/ style="visibility:hidden; ">
              <input type="texxt" name="type" id = "rtype" style="visibility:hidden; ">
              <button id="modsubmit" onclick="send_single()" class="vs-button-text vs-button--text">Send</button>
          </div>
        </div>
        
      
    </div>
  </div>
    <!-- End: Send Sms-->


    <!-- BEGIN: Vendor JS-->
    <script src="<?= $assets?>app-assets/vendors/js/vendors.min.js"></script>
    <!-- BEGIN Vendor JS-->

    <!-- BEGIN: Page Vendor JS-->
    <script src="<?= $assets?>app-assets/vendors/js/charts/apexcharts.min.js"></script>
    <script src="<?= $assets?>app-assets/vendors/js/extensions/tether.min.js"></script>
    <script src="<?= $assets?>app-assets/vendors/js/extensions/shepherd.min.js"></script>
    <!-- END: Page Vendor JS-->

    <!-- BEGIN: Theme JS-->
    <script src="<?= $assets?>app-assets/js/core/app-menu.js"></script>
    <script src="<?= $assets?>app-assets/js/core/app.js"></script>
    <script src="<?= $assets?>app-assets/js/scripts/components.js"></script>
    <!-- END: Theme JS-->
    <!---all tags--->
    <?php
    if(isset($js))
    {
        foreach ($js as $key => $value) {
            ?>
           
            <script src="<?= $value; ?>"></script
            <?php
        }    }
    ?>
        <!-- BEGIN: Page Vendor JS-->
    <script src="<?= $assets?>app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
    <script src="<?= $assets?>app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
    <script src="<?= $assets?>app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
    <script src="<?= $assets?>app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
    <script src="<?= $assets?>app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
    <script src="<?= $assets?>app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
    <script src="<?= $assets?>app-assets/vendors/js/tables/datatable/buttons.bootstrap.min.js"></script>
    <script src="<?= $assets?>app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>

    <!-- BEGIN: Page JS-->
    <script src="<?= $assets?>app-assets/js/scripts/datatables/datatable.js"></script>
    <!-- END: Page JS-->
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>


    <!-- BEGIN: Page JS-->
    <script src="<?= $assets?>app-assets/js/scripts/pages/dashboard-analytics.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <!-- END: Page JS-->
    <script type="text/javascript">
        $(document).ready(function() {
    $('.select2').select2();
});
        function sendSingle(id)
        {
            $('#modsubmit').text('Assign');
            $('#sndSmsModal').modal('show');
            $('#couponID').val(id);

        }
        function send_single(){
            var type=$('#rtype').val();
            var data = {
    number: $('#cnum').val(),
    coupon: $('#couponID').val(),
    type: $('#rtype').val()
  };
            $.post("<?= base_url();?>/api/send_coupon",
  data,
  function(data, status){
    var obj = JSON.parse(data);
    console.log(obj);
    if(obj.status)
    {
        swal("Good job!", obj.msg, "success");
        location.reload();

    }
    else
    {
        swal({
  title: "Sorry!",
  text: obj.msg,
  icon: "error",
});
}
  });
        }
        function send_group()
        {
            var cop = '';
            $('.coupon_check').each(function(i, obj) {
                console.log(i);
                if($(this).prop("checked"))
                {
                    cop += $(this).val()+',';
                    // console.log($(this).val());
                }
            });
     $('#sndSmsModal').modal('show');
            $('#couponID').val(cop);
        }
        function copyToClipboard(id) {
            // var text = $(this).attr('link');
            // console.log(text);
            var link = $("#client"+id).val()+'/'+id;
            alert("Copy Link:"+link);
             
    
  
}
function send_link(type, user)
{
    $('#sndSmsModal').modal('show');
            if(type == 'sms')
            {
                var mid = '#phone'+user;
            $('#cnum').attr('placeholder','Enter Phone Number');
            $('#cnum').val($(mid).text());
            }
            else
            {
                
                $('#cnum').attr('placeholder','Enter Email Address');
            }
            $('#rtype').val(type);
            $('#couponID').val(user);
}

function vid_course()
{
    $('#vid_mod').html('<option value="0">Loading</option');
    $.get("<?= base_url();?>/api/vid_course/"+$('#vidless').val(),{},
  function(data, status){
    if(data)
    {
    $('#vid_mod').html(data);
    }
    else
    {
        alert("No module exist");
    }
});
}
function use_coupon(id)
{
    var data = {
    number: 'null',
    coupon: id,
    type: 'use'
  };
    $.post("<?= base_url();?>/api/send_coupon",
  data,
  function(data, status){
    var obj = JSON.parse(data);
    console.log(obj);
    if(obj.status)
    {
        swal("Good job!", obj.msg, "success");
        location.reload();

    }
    else
    {
        swal({
  title: "Sorry!",
  text: obj.msg,
  icon: "error",
});
}
});
}
    </script>
    <script type="text/javascript">
                    //after_lesson
                    $("#after_lesson").change(function() {
    if(this.checked) {
        $('#lesson_div').show();
    }
    else
    {
        $('#lesson_div').hide();
    }
});
                </script>

</body>
<!-- END: Body-->

</html>