<div class="maincontents signUp login">
		<h1>Login</h1>
		<form action="<?= base_url('/login/post'); ?>" method="post" >
		  <h2>My Account Login</h2>
		  <?php $this->load->view('flash'); ?>
		  <div class="input-container">
		  	<label>Email/Mobile Number</label>
		    <input class="input-field" type="text" placeholder="" name="uname">
		    <i class="fa fa-user"></i>
		  </div>
		  <div class="input-container">
		  	<label>Password</label>
		    <input class="input-field" type="password" placeholder="" name="upass">
		    <i class="fa fa-lock"></i>
		  </div>
			  <input type="submit" class="btn" value="Log in">
			  <a href="sign-up.html">Create a new account!</a>
		  </div>
		  
		</form>

	</div>