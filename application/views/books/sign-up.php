<div class="maincontents signUp">
		<h1>Sign up</h1>
		<form action="<?= base_url('/auth/create'); ?>" method="post" >
		  <h2>Sign up</h2>
		  <?php $this->load->view('flash'); ?>
		  <div class="input-container">
		  	<label>First Name*</label>
		    <input class="input-field" type="text" placeholder="" name="first_name">
		  </div>
		  <div class="input-container">
		  	<label>Last Name*</label>
		    <input class="input-field" type="text" placeholder="" name="last_name">
		  </div>
		  <div class="input-container">
		  	<label>Password*</label>
		    <input class="input-field" type="password" placeholder="" name="upass">
		  </div>
		  <div class="input-container">
		    <label>Confirm Password*</label>
		    <input class="input-field" type="password" placeholder="" name="cpass">
		  </div>
		  <div class="input-container dob">
		    <label>Date of Birth*</label>
		    <input class="input-field" type="text" placeholder="MM" name="">
		    <input class="input-field" type="text" placeholder="DD" name="">
		    <input class="input-field" type="text" placeholder="YYYY" name="">
		    <span>Gender</span>
		    <select class="form-control" id="sel1">
			    <option>Male</option>
			    <option>Female</option>
			</select>
		  </div>
		  <div class="input-container">
		  	<label>Mobile/Cell No.*</label>
		    <input class="input-field" type="text" placeholder="" name="usrnm">
		  </div>
		  <div class="input-container">
		  	<label>E-Mail*</label>
		    <input class="input-field" type="text" placeholder="" name="email">
		  </div>
		  <div class="input-container">
		  	<label>Confirm E-Mail*</label>
		    <input class="input-field" type="text" placeholder="" name="cemail">
		  </div>
		  <div class="input-container">
		  	<label>Address*</label>
		    <textarea rows="5"></textarea>
		  </div>
		  <div class="signUpSmallDiv">
		  	<div class="input-container city">
			  	<label>City*</label>
			    <input class="input-field" type="text" placeholder="" name="">
			</div>
			<div class="input-container state">
			  	<label>State*</label>
			    <input class="input-field" type="text" placeholder="" name="">
			</div>
			<div class="input-container">
			  	<label>Zip/Postal Code*</label>
			    <input class="input-field" type="text" placeholder="" name="">
			</div>
			  <div class="input-container">
			  	<label>Country*</label>
			    <input class="input-field" type="text" placeholder="" name="">
			  </div>
			  <label class="checkbox-inline"><input type="checkbox" value="">Agreed to Terms and Conditions</label>
			  <input type="submit" class="btn" value="Sign Up">
		  </div>
		  
		</form>

	</div>