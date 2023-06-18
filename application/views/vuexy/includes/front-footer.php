<footer id="footer">
			<div class="container">
				<div class="footerLogo">
					<a href="#"><img src="<?= $assets ?>images/pic62.png"></a>
				</div>
				<ul class="social">
					<li><a href="#"><i class="fa fa-facebook-f"></i></a></li>
					<li><a href="#"><i class="fa fa-instagram"></i></a></li>
					<li>Shireenalmutawa</li>
					<li><a href="#"><span>PRIVACY</span></a></li>
					<li><a href="#"><span>TERMS</span></a></li>
				</ul>
			</div> 
		</footer>
    <div class="bottomspace"></div>
	</div>
	
</div>
<script type="text/javascript" src="<?= $assets ?>js/jquery.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/owl.carousel.min.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
    $(document).ready(function() {
      $('.owl-carousel').owlCarousel({
        loop: true,
        margin: 10,
        responsiveClass: true,
        responsive: {
          0: {
            items: 1,
            nav: true
          },
          600: {
            items: 3,
            nav: false
          },
          1000: {
            items: 3,
            nav: true,
            loop: false,
            margin: 20
          }
        }
      })
    })
    function validate_form(form_id)
        {
            var mid = '#'+form_id;
            var error = 1;
            // $(mid+'.required').hide();
            $(mid+'> .required').each(function(i, obj) {
                $(this).removeClass('input_error');
                if($(this).val() == '')
                {
                    error = 0;
                    $(this).addClass('input_error');
                }
            });
            // alert(error);
            return error;
        }
        function form_response(data,form_id)
        {
            var obj = JSON.parse(data);
            var mid = '#'+form_id;
            if(obj['msg'])
            {
                swal("Sorry!", obj['msg'], "error");
            }
            console.log(obj);
            if(obj['red'])
            {
                setTimeout(function(){ 
                    window.location.href = obj['red'];
                      
                  }, 3000);
                
            }
        }
        
    function submit_form(form_id)
{
        var vali =  validate_form(form_id);
        if(vali > 0)
        {
            var mid = '#'+form_id;
            var before_text = ''
            if(form_id == 'login_form')
            {
                before_text = $(mid+' #sbtn').text();
            }
            else
            {
                before_text = $(mid+' button').text();
            }
            
            if(form_id != ' ')
            {
                
                  $.ajax({
                    url: $(mid).attr('action'),
                    method: $(mid).attr('method'),
                    data: $(mid).serialize(),
                    beforeSend: function() {
                        if(form_id == 'login_form')
            {
                $(mid+' #sbtn').text('Loading ...')
            }
            else
            {
                $(mid+' button').text('Loading ...')
            }
                    },
                    success: function(data) {
            
                      setTimeout(function(){ 
                                      if(form_id == 'login_form')
            {
                $(mid+' #sbtn').text(before_text);
            }
            else
            {
                $(mid+' button').text(before_text);
            }
                        form_response(data,form_id);    
                          
                      }, 3000);
                    },
                    error: function() {
                        
                    }
                });
            }
            
        }
        return 0;
}
  </script>
</body>
</html>